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
use MKWebDesign\FilamentWatchdog\Traits\ConfiguresWatchdogNavigation;

class SecurityDashboard extends Page
{
    use ConfiguresWatchdogNavigation;

    protected static function getNavigationVisibility(): string
    {
        return 'always'; // Altijd zichtbaar op alle pagina's
    }
    protected static function getDefaultSecuritySort(): int
    {
        return 1;
    }

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static string $view = 'filament-watchdog::pages.security-dashboard';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Security Dashboard';
    protected static ?string $slug = 'security/dashboard';

    /**
     * Define the sort order for this security item
     * Lower numbers appear first in the menu
     */
    protected static function getSecurityItemSort(): int
    {
        return 1; // Dashboard should be first in the Security menu
    }

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
                    new \Illuminate\Support\HtmlString('
                        ⚠️ WARNING: This will activate a FULL SYSTEM LOCKDOWN including:<br><br>
                        <div style="text-align: left; font-size: 16px;" class="text-left">
                        - <svg class="inline w-4 h-4 text-red-500" fill="#dc2626" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm-3.75 8.25v-3a3.75 3.75 0 117.5 0v3h-7.5z" clip-rule="evenodd" /></svg> Enable maintenance mode (blocks entire site)<br>
                        - <svg class="inline w-4 h-4 text-red-500" fill="#dc2626" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.814 3.051 10.077 9.75 12.98a.75.75 0 00.5 0c6.699-2.903 9.75-7.166 9.75-12.98 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.515 11.209 11.209 0 01-7.877-3.08z" clip-rule="evenodd" /></svg> Block suspicious IP addresses automatically<br>
                        - <svg class="inline w-4 h-4 text-blue-500" fill="#2563eb" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 117.5 0 3.75 3.75 0 01-7.5 0zM15.75 9.75a3 3 0 116 0 3 3 0 01-6 0zM2.25 9.75a3 3 0 116 0 3 3 0 01-6 0zM6.31 15.117A6.745 6.745 0 0112 12a6.745 6.745 0 016.709 7.498.75.75 0 01-.372.568A12.696 12.696 0 0112 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 01-.372.568 6.787 6.787 0 011.019-1.381z" clip-rule="evenodd" /></svg> Clear all user sessions (except yours)<br>
                        - <svg class="inline w-4 h-4 text-green-500" fill="#16a34a" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M15.75 1.5a6.75 6.75 0 00-6.651 7.906c.067.39-.032.717-.221.906l-6.5 6.499a3 3 0 00-.878 2.121v2.818c0 .414.336.75.75.75H6a.75.75 0 00.75-.75v-1.5h1.5A.75.75 0 009 19.5V18h1.5a.75.75 0 00.53-.22l2.658-2.658c.19-.189.517-.288.906-.22A6.75 6.75 0 1015.75 1.5zm0 3a.75.75 0 000 1.5A2.25 2.25 0 0118 8.25a.75.75 0 001.5 0 3.75 3.75 0 00-3.75-3.75z" clip-rule="evenodd" /></svg> Add emergency .htaccess protection<br>
                        - <svg class="inline w-4 h-4 text-blue-500" fill="#2563eb" viewBox="0 0 24 24"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z" /><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z" /></svg> Notify all administrators immediately<br>
                        - <svg class="inline w-4 h-4 text-purple-500" fill="#9333ea" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19.5 21a3 3 0 003-3V9a3 3 0 00-3-3h-5.379a.75.75 0 01-.53-.22L11.47 3.66A2.25 2.25 0 009.879 3H4.5a3 3 0 00-3 3v12a3 3 0 003 3h15zm-6.75-10.5a.75.75 0 00-1.5 0v4.19l-1.72-1.72a.75.75 0 00-1.06 1.06l3 3a.75.75 0 001.06 0l3-3a.75.75 0 10-1.06-1.06l-1.72 1.72V10.5z" clip-rule="evenodd" /></svg> Create emergency backup of critical files<br><br>
                        </div>
                        <strong>Use ONLY in case of active security threats or breaches!</strong><br><br>
                        The entire website will be inaccessible to users until you deactivate the lockdown.
                    ')
                )
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