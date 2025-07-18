<?php

namespace MKWebDesign\FilamentWatchdog\Commands;

use Illuminate\Console\Command;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;
use MKWebDesign\FilamentWatchdog\Models\MalwareDetection;
use MKWebDesign\FilamentWatchdog\Models\ActivityLog;

class DebugCommand extends Command
{
    protected $signature = 'watchdog:debug {--stats : Show database statistics} {--config : Show configuration} {--recent : Show recent activity}';
    protected $description = 'Debug FilamentWatchdog system';

    public function handle(): int
    {
        $this->info('🔍 FilamentWatchdog Debug Information');
        $this->info('=====================================');

        if ($this->option('stats') || (!$this->option('config') && !$this->option('recent'))) {
            $this->showStats();
        }

        if ($this->option('config') || (!$this->option('stats') && !$this->option('recent'))) {
            $this->showConfig();
        }

        if ($this->option('recent') || (!$this->option('stats') && !$this->option('config'))) {
            $this->showRecent();
        }

        return 0;
    }

    private function showStats(): void
    {
        $this->info('📊 Database Statistics');
        $this->info('=====================');

        $fileStats = [
            'Total Files' => FileIntegrityCheck::count(),
            'Clean Files' => FileIntegrityCheck::where('status', 'clean')->count(),
            'New Files' => FileIntegrityCheck::where('status', 'new')->count(),
            'Modified Files' => FileIntegrityCheck::where('status', 'modified')->count(),
            'Deleted Files' => FileIntegrityCheck::where('status', 'deleted')->count(),
        ];

        foreach ($fileStats as $label => $count) {
            $this->line(sprintf('%-20s: %d', $label, $count));
        }

        $this->newLine();

        $securityStats = [
            'Total Alerts' => SecurityAlert::count(),
            'New Alerts' => SecurityAlert::where('status', 'new')->count(),
            'Critical Alerts' => SecurityAlert::where('severity', 'critical')->count(),
            'Malware Detections' => MalwareDetection::count(),
            'Activity Logs' => ActivityLog::count(),
        ];

        foreach ($securityStats as $label => $count) {
            $this->line(sprintf('%-20s: %d', $label, $count));
        }

        $this->newLine();
    }

    private function showConfig(): void
    {
        $this->info('⚙️ Configuration');
        $this->info('================');

        $config = [
            'Monitoring Enabled' => config('filament-watchdog.monitoring.enabled') ? 'Yes' : 'No',
            'Malware Detection' => config('filament-watchdog.malware_detection.enabled') ? 'Yes' : 'No',
            'Activity Monitoring' => config('filament-watchdog.activity_monitoring.enabled') ? 'Yes' : 'No',
            'Alerts Enabled' => config('filament-watchdog.alerts.enabled') ? 'Yes' : 'No',
            'Scan Interval' => config('filament-watchdog.monitoring.scan_interval') . ' seconds',
            'Hash Algorithm' => config('filament-watchdog.file_integrity.hash_algorithm'),
            'Max File Size' => number_format(config('filament-watchdog.file_integrity.max_file_size') / 1024 / 1024, 2) . ' MB',
        ];

        foreach ($config as $label => $value) {
            $this->line(sprintf('%-20s: %s', $label, $value));
        }

        $this->newLine();
        $this->info('📁 Monitored Paths:');
        $paths = config('filament-watchdog.monitoring.monitored_paths', []);
        foreach ($paths as $path) {
            $this->line('  - ' . $path);
        }

        $this->newLine();
        $this->info('🚫 Excluded Paths:');
        $excluded = config('filament-watchdog.monitoring.excluded_paths', []);
        foreach ($excluded as $path) {
            $this->line('  - ' . $path);
        }

        $this->newLine();
    }

    private function showRecent(): void
    {
        $this->info('🕐 Recent Activity (Last 24 hours)');
        $this->info('==================================');

        $this->info('📁 Recent File Changes:');
        $recentFiles = FileIntegrityCheck::where('updated_at', '>=', now()->subDay())
            ->whereIn('status', ['new', 'modified'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        if ($recentFiles->count() > 0) {
            foreach ($recentFiles as $file) {
                $this->line(sprintf(
                    '  %s: %s (%s)',
                    $file->updated_at->format('H:i:s'),
                    $file->file_path,
                    strtoupper($file->status)
                ));
            }
        } else {
            $this->line('  No recent file changes');
        }

        $this->newLine();
        $this->info('🚨 Recent Alerts:');
        $recentAlerts = SecurityAlert::where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($recentAlerts->count() > 0) {
            foreach ($recentAlerts as $alert) {
                $this->line(sprintf(
                    '  %s: %s [%s]',
                    $alert->created_at->format('H:i:s'),
                    $alert->title,
                    strtoupper($alert->severity)
                ));
            }
        } else {
            $this->line('  No recent alerts');
        }

        $this->newLine();
        $this->info('🦠 Recent Malware Detections:');
        $recentMalware = MalwareDetection::where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        if ($recentMalware->count() > 0) {
            foreach ($recentMalware as $malware) {
                $this->line(sprintf(
                    '  %s: %s (%s - %s)',
                    $malware->created_at->format('H:i:s'),
                    $malware->file_path,
                    $malware->threat_type,
                    strtoupper($malware->risk_level)
                ));
            }
        } else {
            $this->line('  No recent malware detections');
        }

        $this->newLine();
    }
}
