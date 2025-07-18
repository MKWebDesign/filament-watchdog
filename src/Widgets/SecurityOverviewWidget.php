<?php

namespace MKWebDesign\FilamentWatchdog\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;
use MKWebDesign\FilamentWatchdog\Models\MalwareDetection;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Models\ActivityLog;

class SecurityOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        try {
            // All time critical alerts
            $criticalAlerts = SecurityAlert::where('severity', 'critical')
                ->whereIn('status', ['new', 'acknowledged'])
                ->count();

            // All time unresolved alerts
            $unresolvedAlerts = SecurityAlert::whereIn('status', ['new', 'acknowledged'])
                ->count();

            // Recent malware (last 24 hours)
            $recentMalware = MalwareDetection::where('created_at', '>=', now()->subDay())
                ->count();

            // Recent file changes (last 24 hours)
            $recentFileChanges = FileIntegrityCheck::where('status', 'modified')
                ->where('created_at', '>=', now()->subDay())
                ->count();

            // Recent high risk activity (last 24 hours)
            $recentHighRiskActivity = ActivityLog::whereIn('risk_level', ['high', 'critical'])
                ->where('created_at', '>=', now()->subDay())
                ->count();

            // Recent alerts (last hour for quick response)
            $recentAlerts = SecurityAlert::where('created_at', '>=', now()->subHour())
                ->count();

        } catch (\Exception $e) {
            // Fallback values if tables don't exist yet
            $criticalAlerts = 0;
            $unresolvedAlerts = 0;
            $recentMalware = 0;
            $recentFileChanges = 0;
            $recentHighRiskActivity = 0;
            $recentAlerts = 0;
        }

        return [
            Stat::make('Unresolved Alerts', $unresolvedAlerts)
                ->description('Total unresolved security alerts')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($unresolvedAlerts > 0 ? 'warning' : 'success')
                ->chart($unresolvedAlerts > 0 ? [1, 3, 2, 4, 3, 2, 1] : [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('Critical Alerts', $criticalAlerts)
                ->description('High priority security issues')
                ->descriptionIcon('heroicon-m-fire')
                ->color($criticalAlerts > 0 ? 'danger' : 'success')
                ->chart($criticalAlerts > 0 ? [3, 5, 4, 6, 5, 4, 3] : [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('Recent Activity', $recentAlerts)
                ->description('Alerts in last hour')
                ->descriptionIcon('heroicon-m-clock')
                ->color($recentAlerts > 0 ? 'info' : 'success')
                ->chart($recentAlerts > 0 ? [2, 4, 3, 5, 4, 3, 2] : [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('File Changes', $recentFileChanges)
                ->description('Modified files (24h)')
                ->descriptionIcon('heroicon-m-document-text')
                ->color($recentFileChanges > 5 ? 'warning' : 'success')
                ->chart($recentFileChanges > 0 ? [1, 2, 3, 2, 1, 2, 1] : [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('Malware Detected', $recentMalware)
                ->description('Threats found (24h)')
                ->descriptionIcon('heroicon-m-bug-ant')
                ->color($recentMalware > 0 ? 'danger' : 'success')
                ->chart($recentMalware > 0 ? [4, 6, 5, 7, 6, 5, 4] : [0, 0, 0, 0, 0, 0, 0]),

            Stat::make('High Risk Activity', $recentHighRiskActivity)
                ->description('Suspicious events (24h)')
                ->descriptionIcon('heroicon-m-eye-slash')
                ->color($recentHighRiskActivity > 0 ? 'danger' : 'success')
                ->chart($recentHighRiskActivity > 0 ? [2, 3, 4, 3, 2, 3, 2] : [0, 0, 0, 0, 0, 0, 0]),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // 2 rows of 3 columns each
    }
}
