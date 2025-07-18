<?php

namespace MKWebDesign\FilamentWatchdog\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use MKWebDesign\FilamentWatchdog\Services\FileIntegrityService;
use MKWebDesign\FilamentWatchdog\Services\MalwareDetectionService;
use MKWebDesign\FilamentWatchdog\Services\AlertService;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Models\MalwareDetection;

class ScanFilesCommand extends Command
{
    protected $signature = 'watchdog:scan {--baseline : Create baseline instead of scanning for changes} {--force : Force scan even if disabled} {--debug : Show detailed debug output}';
    protected $description = 'Scan files for integrity changes and malware';

    public function handle(): int
    {
        if (!config('filament-watchdog.monitoring.enabled') && !$this->option('force')) {
            $this->warn('File monitoring is disabled in configuration.');
            return 1;
        }

        $isBaseline = $this->option('baseline');
        $debug = $this->option('debug');

        $this->info('🔍 Starting FilamentWatchdog security scan...');

        if ($debug) {
            $this->info('Debug mode enabled');
            $this->info('Baseline mode: ' . ($isBaseline ? 'YES' : 'NO'));
        }

        try {
            if ($isBaseline) {
                $this->createBaseline($debug);
            } else {
                $this->scanForChanges($debug);
                $this->scanForDeletedFiles($debug);
                $this->scanForMalware($debug);
            }

            $this->info('✅ Security scan completed successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Scan failed: ' . $e->getMessage());
            if ($debug) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }
            return 1;
        }
    }

    private function createBaseline(bool $debug = false): void
    {
        $this->info('📊 Creating file integrity baseline...');

        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);

        if ($debug) {
            $this->info('Monitored paths: ' . implode(', ', $paths));
            $this->info('Excluded paths: ' . implode(', ', $excluded));
        }

        $totalFiles = 0;

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            if (!File::exists($fullPath)) {
                if ($debug) {
                    $this->warn('Path not found: ' . $fullPath);
                }
                continue;
            }

            $files = $this->getFilesInPath($fullPath, $excluded, $debug);
            $this->info('Processing ' . count($files) . ' files in ' . $path);

            if (count($files) > 0) {
                $bar = $this->output->createProgressBar(count($files));
                $bar->start();

                foreach ($files as $file) {
                    $this->processFileForBaseline($file, $debug);
                    $totalFiles++;
                    $bar->advance();
                }

                $bar->finish();
                $this->newLine();
            }
        }

        $this->info('✅ Baseline created for ' . $totalFiles . ' files.');
    }

    private function scanForChanges(bool $debug = false): void
    {
        $this->info('🔍 Scanning for file changes...');

        $changes = [];
        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            if (!File::exists($fullPath)) {
                continue;
            }

            $files = $this->getFilesInPath($fullPath, $excluded, $debug);

            if ($debug) {
                $this->info('Checking ' . count($files) . ' files in ' . $path);
            }

            foreach ($files as $file) {
                $change = $this->checkFileIntegrity($file, $debug);
                if ($change) {
                    $changes[] = $change;
                }
            }
        }

        if (count($changes) > 0) {
            $this->warn('⚠️  Found ' . count($changes) . ' file changes:');
            foreach ($changes as $change) {
                $this->line('  - ' . $change['status'] . ': ' . $change['path']);
            }
        } else {
            $this->info('✅ No file changes detected.');
        }
    }

    private function scanForDeletedFiles(bool $debug = false): void
    {
        $this->info('🗑️  Scanning for deleted files...');

        $deletedFiles = [];
        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);

        // Get all files currently in database that should be monitored
        $dbFiles = FileIntegrityCheck::whereIn('status', ['clean', 'new', 'modified'])->get();

        foreach ($dbFiles as $dbFile) {
            $fullPath = base_path($dbFile->file_path);

            // Skip if file path is excluded
            if ($this->isExcluded($dbFile->file_path, $excluded)) {
                continue;
            }

            // Skip if file path is not in monitored paths
            $isInMonitoredPath = false;
            foreach ($paths as $path) {
                if (str_starts_with($dbFile->file_path, $path)) {
                    $isInMonitoredPath = true;
                    break;
                }
            }

            if (!$isInMonitoredPath) {
                continue;
            }

            // Check if file still exists
            if (!File::exists($fullPath)) {
                if ($debug) {
                    $this->line('    🗑️  DELETED: ' . $dbFile->file_path);
                }

                // Mark as deleted
                $dbFile->update([
                    'status' => 'deleted',
                    'changes' => [
                        'type' => 'file_deleted',
                        'detected_at' => now()->toISOString(),
                        'previous_hash' => $dbFile->file_hash,
                        'previous_size' => $dbFile->file_size
                    ]
                ]);

                // Create alert for deleted file
                try {
                    app(AlertService::class)->createAlert(
                        'file_deleted',
                        'File Deleted: ' . basename($dbFile->file_path),
                        'A monitored file has been deleted: ' . $dbFile->file_path,
                        'high',
                        [
                            'file_path' => $dbFile->file_path,
                            'previous_hash' => $dbFile->file_hash,
                            'previous_size' => $dbFile->file_size,
                            'detected_at' => now()->toISOString()
                        ]
                    );
                } catch (\Exception $e) {
                    if ($debug) {
                        $this->warn('Failed to create alert for deleted file: ' . $e->getMessage());
                    }
                }

                $deletedFiles[] = ['status' => 'DELETED', 'path' => $dbFile->file_path];
            }
        }

        if (count($deletedFiles) > 0) {
            $this->error('🚨 Found ' . count($deletedFiles) . ' deleted files:');
            foreach ($deletedFiles as $deleted) {
                $this->line('  - ' . $deleted['status'] . ': ' . $deleted['path']);
            }
        } else {
            $this->info('✅ No deleted files detected.');
        }
    }

    private function scanForMalware(bool $debug = false): void
    {
        $this->info('🦠 Scanning for malware...');

        if (!config('filament-watchdog.malware_detection.enabled')) {
            $this->warn('Malware detection is disabled.');
            return;
        }

        $detections = [];
        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);

        foreach ($paths as $path) {
            $fullPath = base_path($path);
            if (!File::exists($fullPath)) {
                continue;
            }

            $files = $this->getFilesInPath($fullPath, $excluded, $debug);

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $detection = $this->scanFileForMalware($file, $debug);
                    if ($detection) {
                        $detections[] = $detection;
                    }
                }
            }
        }

        if (count($detections) > 0) {
            $this->error('🚨 Found ' . count($detections) . ' malware detections:');
            foreach ($detections as $detection) {
                $this->line('  - ' . $detection['threat_type'] . ': ' . $detection['file_path']);
            }
        } else {
            $this->info('✅ No malware detected.');
        }
    }

    private function getFilesInPath(string $path, array $excluded, bool $debug = false): array
    {
        $files = [];

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $filePath = $file->getRealPath();
                    $relativePath = str_replace(base_path() . '/', '', $filePath);

                    if (!$this->isExcluded($relativePath, $excluded)) {
                        $files[] = $filePath;
                        if ($debug) {
                            $this->line('    Found: ' . $relativePath);
                        }
                    } elseif ($debug) {
                        $this->line('    Excluded: ' . $relativePath);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->warn('Error reading path ' . $path . ': ' . $e->getMessage());
        }

        return $files;
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

    private function processFileForBaseline(string $filePath, bool $debug = false): void
    {
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        $hash = hash_file('sha256', $filePath);
        $size = filesize($filePath);
        $lastModified = filemtime($filePath);

        if ($debug) {
            $this->line('    Baseline: ' . $relativePath);
        }

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
    }

    private function checkFileIntegrity(string $filePath, bool $debug = false): ?array
    {
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        $hash = hash_file('sha256', $filePath);
        $size = filesize($filePath);
        $lastModified = filemtime($filePath);

        $existing = FileIntegrityCheck::where('file_path', $relativePath)->first();

        if (!$existing) {
            // New file detected
            if ($debug) {
                $this->line('    🆕 NEW FILE: ' . $relativePath);
            }

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

            // Create alert for new file
            try {
                app(AlertService::class)->createAlert(
                    'new_file_detected',
                    'New File Detected: ' . basename($relativePath),
                    'A new file has been detected in the monitored directories: ' . $relativePath,
                    'medium',
                    [
                        'file_path' => $relativePath,
                        'file_size' => $size,
                        'detected_at' => now()->toISOString()
                    ]
                );
            } catch (\Exception $e) {
                if ($debug) {
                    $this->warn('Failed to create alert: ' . $e->getMessage());
                }
            }

            return ['status' => 'NEW', 'path' => $relativePath];
        }

        if ($existing->file_hash !== $hash) {
            // Modified file
            if ($debug) {
                $this->line('    🔄 MODIFIED: ' . $relativePath);
            }

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

            // Create alert for modified file
            try {
                app(AlertService::class)->createAlert(
                    'file_modified',
                    'File Modified: ' . basename($relativePath),
                    'A monitored file has been modified: ' . $relativePath,
                    'medium',
                    ['file_path' => $relativePath, 'changes' => $changes]
                );
            } catch (\Exception $e) {
                if ($debug) {
                    $this->warn('Failed to create alert: ' . $e->getMessage());
                }
            }

            return ['status' => 'MODIFIED', 'path' => $relativePath];
        }

        if ($debug) {
            $this->line('    ✓ Clean: ' . $relativePath);
        }

        return null;
    }

    private function scanFileForMalware(string $filePath, bool $debug = false): ?array
    {
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        $content = File::get($filePath);
        $signatures = config('filament-watchdog.malware_detection.signatures', []);

        foreach ($signatures as $threatType => $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                if ($debug) {
                    $this->line('    🦠 MALWARE: ' . $relativePath . ' (' . $threatType . ')');
                }

                $detection = [
                    'file_path' => $relativePath,
                    'threat_type' => $threatType,
                    'signature_matched' => $pattern,
                    'threat_details' => [
                        'matched_text' => $matches[0] ?? '',
                        'file_size' => filesize($filePath),
                        'scan_time' => now()->toISOString(),
                        'line_number' => $this->getLineNumber($content, $matches[0] ?? '')
                    ],
                    'risk_level' => $this->calculateRiskLevel($threatType),
                    'status' => 'detected',
                ];

                MalwareDetection::create($detection);

                // Create critical alert
                try {
                    app(AlertService::class)->createAlert(
                        'malware_detected',
                        'Malware Detected: ' . basename($relativePath),
                        'Malware threat detected in file: ' . $relativePath . ' (Type: ' . $threatType . ')',
                        'critical',
                        [
                            'file_path' => $relativePath,
                            'threat_type' => $threatType,
                            'risk_level' => $this->calculateRiskLevel($threatType)
                        ]
                    );
                } catch (\Exception $e) {
                    if ($debug) {
                        $this->warn('Failed to create alert: ' . $e->getMessage());
                    }
                }

                return $detection;
            }
        }

        return null;
    }

    private function getLineNumber(string $content, string $match): int
    {
        if (empty($match)) {
            return 0;
        }

        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            if (str_contains($line, $match)) {
                return $lineNumber + 1;
            }
        }
        return 0;
    }

    private function calculateRiskLevel(string $threatType): string
    {
        $highRiskPatterns = ['php_system', 'php_exec', 'php_shell_exec', 'web_shell'];
        $mediumRiskPatterns = ['php_eval', 'file_get_contents', 'curl_exec'];

        if (in_array($threatType, $highRiskPatterns)) {
            return 'critical';
        } elseif (in_array($threatType, $mediumRiskPatterns)) {
            return 'high';
        } else {
            return 'medium';
        }
    }
}
