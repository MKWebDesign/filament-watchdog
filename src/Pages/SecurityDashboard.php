<?php

namespace MKWebDesign\FilamentWatchdog\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use MKWebDesign\FilamentWatchdog\Widgets\SecurityOverviewWidget;
use MKWebDesign\FilamentWatchdog\Widgets\ThreatLevelWidget;
use MKWebDesign\FilamentWatchdog\Widgets\RecentAlertsWidget;
use MKWebDesign\FilamentWatchdog\Models\SecurityAlert;
use MKWebDesign\FilamentWatchdog\Models\FileIntegrityCheck;
use MKWebDesign\FilamentWatchdog\Models\MalwareDetection;
use MKWebDesign\FilamentWatchdog\Services\FileIntegrityService;
use MKWebDesign\FilamentWatchdog\Services\MalwareDetectionService;
use MKWebDesign\FilamentWatchdog\Services\AlertService;
use MKWebDesign\FilamentWatchdog\Services\EmergencyLockdownService;
use Illuminate\Support\Facades\File;

class SecurityDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static string $view = 'filament-watchdog::pages.security-dashboard';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Security Dashboard';
    protected static ?string $slug = 'security/dashboard';

    protected function getHeaderActions(): array
    {
        $lockdownService = app(EmergencyLockdownService::class);
        $isLockdownActive = $lockdownService->isLockdownActive();

        return [
            Action::make('runScan')
                ->label('Run Manual Scan')
                ->icon('heroicon-o-magnifying-glass')
                ->color('primary')
                ->requiresConfirmation()
        ->modalHeading('Run Security Scan')
                ->modalDescription('This will scan all files for changes and malware. This may take a few minutes.')
                ->modalSubmitActionLabel('Start Scan')
                ->action(function () {
        try {
            $fileIntegrityService = app(FileIntegrityService::class);
            $malwareDetectionService = app(MalwareDetectionService::class);

            $changes = $fileIntegrityService->scanForChanges();
            $malwareDetections = $malwareDetectionService->scanUploads();

            $changeCount = count($changes);
            $malwareCount = count($malwareDetections);

            Notification::make()
                ->title('Security Scan Completed')
                            ->body('Found ' . $changeCount . ' file changes and ' . $malwareCount . ' malware detections.')
                            ->success()
                ->send();

                        $this->redirect(static::getUrl());
                    } catch (\Exception $e) {
            Notification::make()
                ->title('Scan Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                ->send();
                    }
    }),

            Action::make('createBaseline')
                ->label('Create Baseline')
                ->icon('heroicon-o-document-duplicate')
                ->color('success')
                ->requiresConfirmation()
        ->modalHeading('Create New Baseline')
                ->modalDescription('This will create a new baseline of all files. Existing change records will be reset.')
                ->modalSubmitActionLabel('Create Baseline')
                ->action(function () {
        try {
            $fileIntegrityService = app(FileIntegrityService::class);
            $fileIntegrityService->createBaseline();

            $totalFiles = FileIntegrityCheck::count();

            Notification::make()
                ->title('Baseline Created')
                            ->body('New security baseline created for ' . $totalFiles . ' files.')
                            ->success()
                ->send();

                        $this->redirect(static::getUrl());
                    } catch (\Exception $e) {
            Notification::make()
                ->title('Baseline Creation Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                ->send();
                    }
    }),

            Action::make('viewQuarantine')
                ->label('View Quarantine')
                ->icon('heroicon-o-archive-box')
                ->color('warning')
                ->action(function () {
        $quarantinePath = config('filament-watchdog.malware_detection.quarantine_path');

                    if (!$quarantinePath) {
                        $quarantinePath = storage_path('app/quarantine');
                    }

                    $quarantineExists = File::exists($quarantinePath);

                    if ($quarantineExists) {
                        $files = File::files($quarantinePath);
                        $fileCount = count($files);

                        if ($fileCount > 0) {
                            $fileList = collect($files)->take(5)->map(function ($file) {
                                return basename($file);
                            })->join(', ');

                            Notification::make()
                                ->title('Quarantine Status')
                                ->body('Found ' . $fileCount . ' quarantined file(s): ' . $fileList . ($fileCount > 5 ? ' and ' . ($fileCount - 5) . ' more...' : ''))
                                ->warning()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Quarantine Empty')
                                ->body('Quarantine directory exists but contains no files.')
                                ->success()
                                ->send();
                        }
                    } else {
                        Notification::make()
                            ->title('Quarantine Not Found')
                            ->body('Quarantine directory does not exist: ' . $quarantinePath)
                            ->info()
                            ->send();
                    }
                }),

            // Enhanced Emergency Lockdown Action
            Action::make($isLockdownActive ? 'deactivateLockdown' : 'emergencyLockdown')
                ->label($isLockdownActive ? '🔓 Deactivate Lockdown' : '🚨 Emergency Lockdown')
                ->icon($isLockdownActive ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
                ->color($isLockdownActive ? 'success' : 'danger')
                ->requiresConfirmation()
        ->modalHeading($isLockdownActive ? '🔓 Deactivate Emergency Lockdown' : '⚠️ ACTIVATE EMERGENCY LOCKDOWN')
                ->modalDescription($isLockdownActive ?
        'This will deactivate the emergency lockdown and restore normal system operations. Users will regain access to the website.' :
                    '⚠️ WARNING: This will activate a FULL SYSTEM LOCKDOWN including:

• 🚫 Enable maintenance mode (blocks entire website)
• 🔒 Block suspicious IP addresses automatically  
• 👥 Clear all user sessions (except yours)
• 🛡️ Add emergency .htaccess protection
• 📧 Notify all administrators immediately
• 💾 Create emergency backup of critical files

Use ONLY in case of active security threats or breaches!

The entire website will be inaccessible to users until you deactivate the lockdown.')
                ->modalSubmitActionLabel($isLockdownActive ? 'Deactivate Lockdown' : '🚨 ACTIVATE LOCKDOWN')
                ->action(function () use ($lockdownService, $isLockdownActive) {
        try {
            if ($isLockdownActive) {
                // Deactivate lockdown
                $results = $lockdownService->deactivateEmergencyLockdown();

                if ($results['status'] === 'success') {
                    Notification::make()
                        ->title('✅ Emergency Lockdown Deactivated')
                                    ->body('System lockdown has been deactivated. Normal operations resumed. Users restored: ' . ($results['users_restored'] ?? 0))
                                    ->success()
                        ->persistent()
                        ->send();
                            } else {
                    throw new \Exception($results['error'] ?? 'Unknown error during deactivation');
                            }
                        } else {
                // Activate lockdown with default options
                $results = $lockdownService->activateEmergencyLockdown([
                    'maintenance_mode' => true,
                                'block_ips' => true,
                                'disable_users' => false, // Keep users active but they cant access due to maintenance
                                'clear_sessions' => true,
                                'htaccess_protection' => true,
                                'notify_admins' => true,
                                'emergency_backup' => true
                            ]);

                            if ($results['status'] === 'success') {
                    $accessUrl = $lockdownService->getEmergencyAccessUrl();

                    Notification::make()
                        ->title('🚨 EMERGENCY LOCKDOWN ACTIVATED')
                                    ->body('Critical security lockdown activated! Alert ID: ' . $results['alert_id'] . '
                                    
🔑 Emergency Access URL: ' . $accessUrl . '
📧 Administrators notified: ' . ($results['admin_notifications'] ?? 0) . '
🚫 IPs blocked: ' . count($results['blocked_ips'] ?? []) . '
💾 Emergency backup: ' . ($results['emergency_backup'] ? '✅ Created' : '❌ Failed') . '

⚠️ WEBSITE IS NOW IN MAINTENANCE MODE - Only you can access it!')
                                    ->danger()
                        ->persistent()
                        ->send();
                            } else {
                    throw new \Exception($results['error'] ?? 'Unknown error during activation');
                            }
                        }

            $this->redirect(static::getUrl());
        } catch (\Exception $e) {
            Notification::make()
                ->title($isLockdownActive ? 'Lockdown Deactivation Failed' : 'Lockdown Activation Failed')
                            ->body('Error: ' . $e->getMessage())
                            ->danger()
                ->send();
                    }
    }),

            Action::make('lockdownStatus')
                ->label('Lockdown Status')
                ->icon('heroicon-o-information-circle')
                ->color('gray')
                ->visible($isLockdownActive)
        ->action(function () use ($lockdownService) {
            $status = $lockdownService->getLockdownStatus();
            $accessUrl = $lockdownService->getEmergencyAccessUrl();

            if ($status) {
                Notification::make()
                    ->title('🚨 Emergency Lockdown Status')
                            ->body('Lockdown ID: ' . $status['lockdown_id'] . '
Activated by: ' . $status['activated_by'] . '
Activated at: ' . $status['activated_at']->format('Y-m-d H:i:s') . '

🔑 Emergency Access: ' . $accessUrl)
                            ->info()
                    ->persistent()
                    ->send();
                    }
        }),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SecurityOverviewWidget::class,
            ThreatLevelWidget::class,
            RecentAlertsWidget::class,
        ];
    }

    public function getTitle(): string
    {
        return 'Security Dashboard';
    }

    public function getHeading(): string
    {
        $lockdownService = app(EmergencyLockdownService::class);
        $isLockdownActive = $lockdownService->isLockdownActive();

        if ($isLockdownActive) {
            return '🚨 Security Dashboard - EMERGENCY LOCKDOWN ACTIVE';
        }

        return 'Security Dashboard';
    }

    protected function getViewData(): array
    {
        $lockdownService = app(EmergencyLockdownService::class);

        return [
            'systemStatus' => [
        'fileMonitoring' => config('filament-watchdog.monitoring.enabled', true),
                'malwareDetection' => config('filament-watchdog.malware_detection.enabled', true),
                'activityMonitoring' => config('filament-watchdog.activity_monitoring.enabled', true),
                'alertSystem' => config('filament-watchdog.alerts.enabled', true),
                'emergencyLockdown' => $lockdownService->isLockdownActive(),
            ],
            'stats' => [
        'totalFiles' => FileIntegrityCheck::count(),
                'modifiedFiles' => FileIntegrityCheck::where('status', 'modified')->count(),
                'malwareDetections' => MalwareDetection::count(),
                'unresolvedAlerts' => SecurityAlert::whereIn('status', ['new', 'acknowledged'])->count(),
            ],
            'lockdownStatus' => $lockdownService->getLockdownStatus(),
        ];
    }
}
