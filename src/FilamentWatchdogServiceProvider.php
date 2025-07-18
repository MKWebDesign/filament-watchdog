<?php

namespace MKWebDesign\FilamentWatchdog;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\File;
use MKWebDesign\FilamentWatchdog\Commands\ScanFilesCommand;
use MKWebDesign\FilamentWatchdog\Commands\CreateBaselineCommand;
use MKWebDesign\FilamentWatchdog\Commands\CleanupLogsCommand;
use MKWebDesign\FilamentWatchdog\Commands\EmergencyLockdownCommand;
use MKWebDesign\FilamentWatchdog\Commands\PublishViewsCommand;
use MKWebDesign\FilamentWatchdog\Services\FileIntegrityService;
use MKWebDesign\FilamentWatchdog\Services\MalwareDetectionService;
use MKWebDesign\FilamentWatchdog\Services\ActivityMonitoringService;
use MKWebDesign\FilamentWatchdog\Services\AlertService;
use MKWebDesign\FilamentWatchdog\Services\EmergencyLockdownService;

class FilamentWatchdogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/filament-watchdog.php', 'filament-watchdog');

        // Register services as singletons
        $this->app->singleton(FileIntegrityService::class);
        $this->app->singleton(MalwareDetectionService::class);
        $this->app->singleton(ActivityMonitoringService::class);
        $this->app->singleton(AlertService::class);
        $this->app->singleton(EmergencyLockdownService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-watchdog');

        // Publish config
        $this->publishes([
            __DIR__ . '/../config/filament-watchdog.php' => config_path('filament-watchdog.php'),
        ], 'filament-watchdog-config');

        // Publish package views
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-watchdog'),
        ], 'filament-watchdog-views');

        // Publish emergency error views directly to Laravel's error views
        $this->publishes([
            __DIR__ . '/../resources/views/errors' => resource_path('views/errors'),
        ], 'filament-watchdog-errors');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ScanFilesCommand::class,
                CreateBaselineCommand::class,
                CleanupLogsCommand::class,
                EmergencyLockdownCommand::class,
                PublishViewsCommand::class, // New command for publishing views
            ]);
        }

        // Auto-publish emergency views on installation (if enabled in config)
        if (config('filament-watchdog.emergency.auto_publish_views', true)) {
            $this->autoPublishEmergencyViews();
        }

        // Register scheduled commands
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            // Run scan every minute if enabled
            if (config('filament-watchdog.monitoring.enabled', true)) {
                $schedule->command('watchdog:scan')->everyMinute();
            }

            // Cleanup logs daily
            $schedule->command('watchdog:cleanup', ['--force'])->daily();
        });
    }

    /**
     * Automatically publish emergency views during package installation
     */
    private function autoPublishEmergencyViews(): void
    {
        // Only auto-publish if views don't exist yet
        $errorViewsPath = resource_path('views/errors');
        $emergencyViewPath = $errorViewsPath . '/emergency-lockdown.blade.php';

        if (!File::exists($emergencyViewPath)) {
            try {
                // Ensure errors directory exists
                if (!File::exists($errorViewsPath)) {
                    File::makeDirectory($errorViewsPath, 0755, true);
                }

                // Copy emergency view template from package
                $sourceView = __DIR__ . '/../resources/views/errors/emergency-lockdown.blade.php';

                if (File::exists($sourceView)) {
                    File::copy($sourceView, $emergencyViewPath);

                    // Log successful auto-publishing
                    if (function_exists('info')) {
                        info('FilamentWatchdog: Emergency maintenance view auto-published successfully');
                    }
                } else {
                    // Fallback: create basic emergency view
                    $this->createFallbackEmergencyView($emergencyViewPath);
                }
            } catch (\Exception $e) {
                // Silent fail - don't break installation
                if (function_exists('error_log')) {
                    error_log('FilamentWatchdog: Failed to auto-publish emergency view: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     * Create fallback emergency view if package view is not available
     */
    private function createFallbackEmergencyView(string $targetPath): void
    {
        $emergencyContent = $this->getBasicEmergencyTemplate();
        File::put($targetPath, $emergencyContent);
    }

    /**
     * Get basic emergency template as fallback
     */
    private function getBasicEmergencyTemplate(): string
    {
        return '<!DOCTYPE html>
<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 Emergency Security Lockdown</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }
        .container {
            text-align: center;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }
        p {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        .status {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
        }
        .footer {
            margin-top: 40px;
            font-size: 0.9rem;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚨</div>
        <h1>Emergency Security Lockdown</h1>
        <p>
            Our security system has temporarily restricted access to protect your data. 
            We are working to resolve this situation as quickly as possible.
        </p>
        <div class="status">
            <p><strong>Security measures active:</strong></p>
            <ul style="text-align: left; margin: 15px 0;">
                <li>🔒 Access restricted to administrators</li>
                <li>🧹 User sessions cleared</li>
                <li>💾 Emergency backup created</li>
                <li>📧 Administrators notified</li>
            </ul>
        </div>
        <div class="footer">
            <p>🐕 <strong>FilamentWatchdog</strong> - Advanced Security Protection</p>
            <p>Expected resolution: Within 1-2 hours</p>
        </div>
    </div>
    <script>
        // Auto-refresh every 5 minutes
        setTimeout(function() {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>';
    }

    /**
     * Get the services provided by the provider
     */
    public function provides(): array
    {
        return [
            FileIntegrityService::class,
            MalwareDetectionService::class,
            ActivityMonitoringService::class,
            AlertService::class,
            EmergencyLockdownService::class,
            ScanFilesCommand::class,
            CreateBaselineCommand::class,
            CleanupLogsCommand::class,
            EmergencyLockdownCommand::class,
            PublishViewsCommand::class,
        ];
    }
}