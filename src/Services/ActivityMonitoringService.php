<?php

namespace MKWebDesign\FilamentWatchdog\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use MKWebDesign\FilamentWatchdog\Models\ActivityLog;

class ActivityMonitoringService
{
    public function __construct(
        private AlertService $alertService
    ) {}

public function logActivity(string $eventType, array $details = [], string $riskLevel = 'low'): void
{
    $log = ActivityLog::create([
        'event_type' => $eventType,
        'user_id' => Auth::id(),
        'ip_address' => Request::ip(),
        'user_agent' => Request::userAgent(),
        'event_details' => $details,
        'risk_level' => $riskLevel,
        'metadata' => [
            'timestamp' => now(),
            'session_id' => session()->getId(),
            'url' => Request::fullUrl(),
            'method' => Request::method(),
        ],
    ]);

    if (in_array($riskLevel, ['high', 'critical'])) {
        $this->alertService->createAlert(
            'suspicious_activity',
            "Suspicious Activity Detected",
            "High-risk activity detected: {$eventType}",
            $riskLevel,
            ['activity_log_id' => $log->id, 'details' => $details]
        );
    }
}

public function logFailedLogin(string $email, string $reason = 'invalid_credentials'): void
{
    $ipAddress = Request::ip();
    $recentFailures = ActivityLog::where('ip_address', $ipAddress)
        ->where('event_type', 'failed_login')
        ->where('created_at', '>=', now()->subHour())
        ->count();

    $riskLevel = $recentFailures >= config('filament-watchdog.activity_monitoring.failed_login_threshold', 5)
        ? 'high'
        : 'medium';

    $this->logActivity('failed_login', [
        'email' => $email,
        'reason' => $reason,
        'attempt_count' => $recentFailures + 1,
    ], $riskLevel);

    if ($riskLevel === 'high') {
        $this->alertService->createAlert(
            'brute_force_attempt',
            "Brute Force Attack Detected",
            "Multiple failed login attempts from IP: {$ipAddress}",
            'critical',
            ['ip_address' => $ipAddress, 'attempt_count' => $recentFailures + 1]
        );
    }
}

public function logSuccessfulLogin(string $email): void
{
    $this->logActivity('successful_login', [
        'email' => $email,
        'user_agent' => Request::userAgent(),
    ]);
}

public function logAdminAction(string $action, array $details = []): void
{
    $this->logActivity('admin_action', array_merge([
        'action' => $action,
        'user_id' => Auth::id(),
    ], $details), 'medium');
}

public function logFileChange(string $filePath, string $changeType, array $details = []): void
{
    $riskLevel = in_array($changeType, ['deleted', 'modified']) ? 'medium' : 'low';

    $this->logActivity('file_change', array_merge([
        'file_path' => $filePath,
        'change_type' => $changeType,
    ], $details), $riskLevel);
}

public function logDatabaseChange(string $table, string $operation, array $details = []): void
{
    $sensitiveOperations = ['DROP', 'TRUNCATE', 'DELETE'];
    $riskLevel = in_array(strtoupper($operation), $sensitiveOperations) ? 'high' : 'medium';

    $this->logActivity('database_change', array_merge([
        'table' => $table,
        'operation' => $operation,
    ], $details), $riskLevel);
}

public function getRecentActivity(int $hours = 24): array
{
    return ActivityLog::where('created_at', '>=', now()->subHours($hours))
        ->orderBy('created_at', 'desc')
        ->get()
        ->toArray();
}

public function getActivityStats(): array
{
    $stats = [
        'total_events' => ActivityLog::count(),
        'high_risk_events' => ActivityLog::whereIn('risk_level', ['high', 'critical'])->count(),
        'failed_logins' => ActivityLog::where('event_type', 'failed_login')->count(),
        'admin_actions' => ActivityLog::where('event_type', 'admin_action')->count(),
    ];

    $stats['recent_events'] = ActivityLog::where('created_at', '>=', now()->subDay())->count();
    $stats['unique_ips'] = ActivityLog::distinct('ip_address')->count();

    return $stats;
}
}