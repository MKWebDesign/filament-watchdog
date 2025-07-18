<?php

namespace MKWebDesign\FilamentWatchdog\Commands;

use Illuminate\Console\Command;
use MKWebDesign\FilamentWatchdog\Models\ActivityLog;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Models\MalwareDetection;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;

class CleanupLogsCommand extends Command
{
    protected $signature = 'watchdog:cleanup {--days=30 : Number of days to keep logs} {--force : Force cleanup without confirmation}';
    protected $description = 'Clean up old logs and maintain database performance';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $retentionDays = $days ?: config('filament-watchdog.database.log_retention_days', 30);
        $cutoffDate = now()->subDays($retentionDays);

        $this->info('🧹 Cleaning up FilamentWatchdog logs older than ' . $retentionDays . ' days...');

        if (!$this->option('force')) {
            if (!$this->confirm('This will permanently delete old security logs. Continue?')) {
                $this->info('Cleanup cancelled.');
                return 0;
            }
        }

        try {
            $deletedActivity = ActivityLog::where('created_at', '<', $cutoffDate)->delete();
            $deletedIntegrity = FileIntegrityCheck::where('created_at', '<', $cutoffDate)
                ->where('status', '!=', 'modified') // Keep modified files
                ->delete();
            $deletedMalware = MalwareDetection::where('created_at', '<', $cutoffDate)
                ->where('status', 'cleaned') // Only delete cleaned malware
                ->delete();
            $deletedAlerts = SecurityAlert::where('created_at', '<', $cutoffDate)
                ->where('status', 'resolved') // Only delete resolved alerts
                ->delete();

            $this->info('✅ Cleanup completed:');
            $this->line('  - Activity logs: ' . $deletedActivity . ' deleted');
            $this->line('  - File integrity checks: ' . $deletedIntegrity . ' deleted');
            $this->line('  - Malware detections: ' . $deletedMalware . ' deleted');
            $this->line('  - Security alerts: ' . $deletedAlerts . ' deleted');

            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Cleanup failed: ' . $e->getMessage());
            return 1;
        }
    }
}
