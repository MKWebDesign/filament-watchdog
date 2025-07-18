<?php

namespace MKWebDesign\FilamentWatchdog\Commands;

use Illuminate\Console\Command;
use MKWebDesign\FilamentWatchdog\Services\EmergencyLockdownService;

class EmergencyLockdownCommand extends Command
{
    protected $signature = 'watchdog:emergency-lockdown 
                            {action : activate or deactivate}
                            {--maintenance-mode=1 : Enable maintenance mode (1 or 0)}
                            {--block-ips=1 : Block suspicious IPs (1 or 0)}
                            {--disable-users=0 : Disable non-admin users (1 or 0)}
                            {--clear-sessions=1 : Clear user sessions (1 or 0)}
                            {--htaccess-protection=1 : Add htaccess protection (1 or 0)}
                            {--notify-admins=1 : Notify administrators (1 or 0)}
                            {--emergency-backup=1 : Create emergency backup (1 or 0)}
                            {--force : Skip confirmation}';

    protected $description = 'Activate or deactivate emergency security lockdown';

    public function handle(EmergencyLockdownService $lockdownService): int
    {
        $action = $this->argument('action');

        if (!in_array($action, ['activate', 'deactivate'])) {
        $this->error('Action must be either \"activate\" or \"deactivate\"');
            return 1;
        }

        if ($action === 'activate') {
        return $this->activateLockdown($lockdownService);
    } else {
        return $this->deactivateLockdown($lockdownService);
    }
    }

    private function activateLockdown(EmergencyLockdownService $lockdownService): int
    {
        if ($lockdownService->isLockdownActive()) {
            $this->warn('⚠️  Emergency lockdown is already active!');
            
            $status = $lockdownService->getLockdownStatus();
            if ($status) {
                $this->info('Lockdown ID: ' . $status['lockdown_id']);
                $this->info('Activated by: ' . $status['activated_by']);
                $this->info('Activated at: ' . $status['activated_at']->format('Y-m-d H:i:s'));
            }
            
            return 0;
        }

        $this->warn('🚨 WARNING: This will activate EMERGENCY LOCKDOWN!');
        $this->warn('This will block access to the entire website except for admins.');

        if (!$this->option('force') && !$this->confirm('Do you want to continue?')) {
        $this->info('Emergency lockdown cancelled.');
            return 0;
        }

        $options = [
            'maintenance_mode' => (bool) $this->option('maintenance-mode'),
            'block_ips' => (bool) $this->option('block-ips'),
            'disable_users' => (bool) $this->option('disable-users'),
            'clear_sessions' => (bool) $this->option('clear-sessions'),
            'htaccess_protection' => (bool) $this->option('htaccess-protection'),
            'notify_admins' => (bool) $this->option('notify-admins'),
            'emergency_backup' => (bool) $this->option('emergency-backup'),
        ];

        $this->info('🔒 Activating emergency lockdown...');

        $results = $lockdownService->activateEmergencyLockdown($options);

        if ($results['status'] === 'success') {
        $this->info('✅ Emergency lockdown activated successfully!');
            $this->info('Alert ID: ' . $results['alert_id']);
            
            if (isset($results['blocked_ips']) && count($results['blocked_ips']) > 0) {
            $this->info('Blocked IPs: ' . implode(', ', $results['blocked_ips']));
            }
            
            $accessUrl = $lockdownService->getEmergencyAccessUrl();
            if ($accessUrl) {
                $this->warn('🔑 Emergency access URL: ' . $accessUrl);
                $this->warn('Save this URL to access the system during lockdown!');
            }
            
            return 0;
        } else {
        $this->error('❌ Failed to activate emergency lockdown: ' . ($results['error'] ?? 'Unknown error'));
            return 1;
        }
    }

    private function deactivateLockdown(EmergencyLockdownService $lockdownService): int
    {
        if (!$lockdownService->isLockdownActive()) {
            $this->warn('⚠️  No emergency lockdown is currently active.');
            return 0;
        }

        $this->info('🔓 Deactivating emergency lockdown...');

        if (!$this->option('force') && !$this->confirm('Do you want to deactivate the emergency lockdown?')) {
        $this->info('Deactivation cancelled.');
            return 0;
        }

        $results = $lockdownService->deactivateEmergencyLockdown();

        if ($results['status'] === 'success') {
        $this->info('✅ Emergency lockdown deactivated successfully!');
            $this->info('Maintenance mode disabled: ' . ($results['maintenance_disabled'] ? 'Yes' : 'No'));
            $this->info('Users restored: ' . ($results['users_restored'] ?? 0));
            $this->info('Normal operations resumed.');
            return 0;
        } else {
        $this->error('❌ Failed to deactivate emergency lockdown: ' . ($results['error'] ?? 'Unknown error'));
            return 1;
        }
    }
}
