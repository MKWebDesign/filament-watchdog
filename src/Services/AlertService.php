<?php

namespace MKWebDesign\FilamentWatchdog\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;

class AlertService
{
    public function createAlert(
        string $alertType,
        string $title,
        string $description,
        string $severity = 'medium',
        array $metadata = []
    ): SecurityAlert {
        $alert = SecurityAlert::create([
            'alert_type' => $alertType,
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'status' => 'new',
            'metadata' => $metadata,
        ]);

        if (config('filament-watchdog.alerts.enabled')) {
            $this->sendAlert($alert);
        }

        return $alert;
    }

    private function sendAlert(SecurityAlert $alert): void
    {
        if (!config('filament-watchdog.alerts.email_enabled')) {
            return;
        }

        if ($this->isRateLimited()) {
            return;
        }

        $recipients = config('filament-watchdog.alerts.email_recipients', []);

        foreach ($recipients as $recipient) {
            try {
                Mail::send([], [], function ($message) use ($alert, $recipient) {
                    $message->to($recipient)
                        ->subject('[SECURITY ALERT] ' . $alert->title)
                        ->html($this->buildEmailContent($alert));
                });
            } catch (\Exception $e) {
                \Log::error('Failed to send security alert email: ' . $e->getMessage());
            }
        }

        $this->incrementRateLimit();
    }

    private function buildEmailContent(SecurityAlert $alert): string
    {
        $severity = strtoupper($alert->severity);
        $timestamp = $alert->created_at->format('Y-m-d H:i:s');

        return "
        <h2 style='color: #dc3545;'>🚨 SECURITY ALERT - {$severity}</h2>
        <h3>{$alert->title}</h3>
        <p><strong>Description:</strong> {$alert->description}</p>
        <p><strong>Severity:</strong> {$severity}</p>
        <p><strong>Alert Type:</strong> {$alert->alert_type}</p>
        <p><strong>Timestamp:</strong> {$timestamp}</p>
        
        <h4>Additional Details:</h4>
        <pre>" . json_encode($alert->metadata, JSON_PRETTY_PRINT) . "</pre>
        
        <hr>
        <p><em>This is an automated security alert from FilamentWatchdog.</em></p>
        ";
    }

    private function isRateLimited(): bool
    {
        if (!config('filament-watchdog.alerts.rate_limiting.enabled')) {
            return false;
        }

        $key = 'watchdog_alerts_sent';
        $maxAlerts = config('filament-watchdog.alerts.rate_limiting.max_alerts_per_hour', 10);
        $sentCount = Cache::get($key, 0);

        return $sentCount >= $maxAlerts;
    }

    private function incrementRateLimit(): void
    {
        $key = 'watchdog_alerts_sent';
        $currentCount = Cache::get($key, 0);
        Cache::put($key, $currentCount + 1, now()->addHour());
    }

    public function acknowledgeAlert(int $alertId, string $acknowledgedBy): bool
    {
        $alert = SecurityAlert::find($alertId);
        if (!$alert) {
            return false;
        }

        $alert->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $acknowledgedBy,
        ]);

        return true;
    }

    public function resolveAlert(int $alertId, string $resolvedBy): bool
    {
        $alert = SecurityAlert::find($alertId);
        if (!$alert) {
            return false;
        }

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
        ]);

        return true;
    }

    public function getAlertStats(): array
    {
        return [
            'total_alerts' => SecurityAlert::count(),
            'new_alerts' => SecurityAlert::where('status', 'new')->count(),
            'acknowledged_alerts' => SecurityAlert::where('status', 'acknowledged')->count(),
            'resolved_alerts' => SecurityAlert::where('status', 'resolved')->count(),
            'critical_alerts' => SecurityAlert::where('severity', 'critical')->count(),
            'recent_alerts' => SecurityAlert::where('created_at', '>=', now()->subDay())->count(),
        ];
    }
}