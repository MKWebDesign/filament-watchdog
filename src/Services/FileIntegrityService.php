<?php

namespace MKWebDesign\FilamentWatchdog\Services;

use Illuminate\Support\Facades\File;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;

class FileIntegrityService
{
    public function __construct(
        private AlertService $alertService
    ) {}

    public function createBaseline(): void
    {
        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            if (!File::exists($fullPath)) {
                continue;
            }

            $this->scanPath($fullPath, $excluded, true);
        }
    }

    public function scanForChanges(): array
    {
        $changes = [];
        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            if (!File::exists($fullPath)) {
                continue;
            }

            $pathChanges = $this->scanPath($fullPath, $excluded, false);
            $changes = array_merge($changes, $pathChanges);
        }

        return $changes;
    }

    private function scanPath(string $path, array $excluded, bool $isBaseline = false): array
    {
        $changes = [];

        if (!File::exists($path)) {
            return $changes;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getRealPath();
                    $relativePath = str_replace(base_path() . '/', '', $filePath);

                    if ($this->isExcluded($relativePath, $excluded)) {
                        continue;
                    }

                    if ($file->getSize() > config('filament-watchdog.file_integrity.max_file_size', 50 * 1024 * 1024)) {
                        continue;
                    }

                    $change = $this->checkFileIntegrity($filePath, $relativePath, $isBaseline);
                    if ($change && !$isBaseline) {
                        $changes[] = $change;
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but continue
            \Log::error('FilamentWatchdog: Error scanning path ' . $path . ': ' . $e->getMessage());
        }

        return $changes;
    }

    private function checkFileIntegrity(string $filePath, string $relativePath, bool $isBaseline = false): ?array
    {
        $hash = hash_file(config('filament-watchdog.file_integrity.hash_algorithm', 'sha256'), $filePath);
        $size = filesize($filePath);
        $lastModified = filemtime($filePath);

        $existing = FileIntegrityCheck::where('file_path', $relativePath)->first();

        if ($isBaseline) {
            // Always update/create baseline
            FileIntegrityCheck::updateOrCreate(
                ['file_path' => $relativePath],
                [
                    'file_hash' => $hash,
                    'file_size' => $size,
                    'last_modified' => date('Y-m-d H:i:s', $lastModified),
                    'status' => 'clean',
                    'changes' => null,
                ]
            );
            return null;
        }

        if (!$existing) {
            // New file detected during scan
            FileIntegrityCheck::create([
                'file_path' => $relativePath,
                'file_hash' => $hash,
                'file_size' => $size,
                'last_modified' => date('Y-m-d H:i:s', $lastModified),
                'status' => 'new',
                'changes' => [
                    'type' => 'new_file',
                    'detected_at' => now()->toISOString(),
                    'file_size' => $size
                ],
            ]);

            $this->alertService->createAlert(
                'new_file_detected',
                'New File Detected: ' . basename($relativePath),
                'A new file has been detected: ' . $relativePath,
                'medium',
                [
                    'file_path' => $relativePath,
                    'file_size' => $size,
                    'detected_at' => now()->toISOString()
                ]
            );

            return [
                'type' => 'new_file',
                'path' => $relativePath,
                'status' => 'NEW',
                'size' => $size
            ];
        }

        if ($existing->file_hash !== $hash) {
            // Modified file
            $changes = [
                'type' => 'file_modified',
                'old_hash' => $existing->file_hash,
                'new_hash' => $hash,
                'old_size' => $existing->file_size,
                'new_size' => $size,
                'old_modified' => $existing->last_modified,
                'new_modified' => date('Y-m-d H:i:s', $lastModified),
                'detected_at' => now()->toISOString()
            ];

            $existing->update([
                'file_hash' => $hash,
                'file_size' => $size,
                'last_modified' => date('Y-m-d H:i:s', $lastModified),
                'status' => 'modified',
                'changes' => $changes,
            ]);

            $this->alertService->createAlert(
                'file_modified',
                'File Modified: ' . basename($relativePath),
                'The file ' . $relativePath . ' has been modified.',
                'medium',
                ['file_path' => $relativePath, 'changes' => $changes]
            );

            return [
                'type' => 'file_modified',
                'path' => $relativePath,
                'status' => 'MODIFIED',
                'changes' => $changes
            ];
        }

        return null;
    }

    private function isExcluded(string $path, array $excluded): bool
    {
        foreach ($excluded as $excludedPath) {
            if (str_starts_with($path, $excludedPath)) {
                return true;
            }
        }
        return false;
    }

    public function quarantineFile(string $filePath): bool
    {
        $quarantinePath = config('filament-watchdog.malware_detection.quarantine_path');

        if (!File::exists($quarantinePath)) {
            File::makeDirectory($quarantinePath, 0755, true);
        }

        $fileName = basename($filePath);
        $quarantineFile = $quarantinePath . '/' . time() . '_' . $fileName;

        if (File::move($filePath, $quarantineFile)) {
            $this->alertService->createAlert(
                'file_quarantined',
                'File Quarantined: ' . $fileName,
                'The file ' . $filePath . ' has been quarantined to ' . $quarantineFile . '.',
                'high',
                ['original_path' => $filePath, 'quarantine_path' => $quarantineFile]
            );
            return true;
        }

        return false;
    }

    public function getFileStats(): array
    {
        return [
            'total_files' => FileIntegrityCheck::count(),
            'clean_files' => FileIntegrityCheck::where('status', 'clean')->count(),
            'new_files' => FileIntegrityCheck::where('status', 'new')->count(),
            'modified_files' => FileIntegrityCheck::where('status', 'modified')->count(),
            'deleted_files' => FileIntegrityCheck::where('status', 'deleted')->count(),
        ];
    }
}
