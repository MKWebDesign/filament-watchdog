<?php

namespace MKWebDesign\FilamentWatchdog\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishViewsCommand extends Command
{
    protected $signature = 'watchdog:publish-views 
                            {--force : Overwrite existing views}
                            {--only-emergency : Only publish emergency views}
                            {--preview : Preview what will be published}';

    protected $description = 'Publish FilamentWatchdog views including emergency maintenance page';

    public function handle(): int
    {
        $this->info('📦 Publishing FilamentWatchdog Views...');
        $this->newLine();

        $onlyEmergency = $this->option('only-emergency');
        $force = $this->option('force');
        $preview = $this->option('preview');

        if ($preview) {
            return $this->showPreview();
        }

        if ($onlyEmergency) {
            return $this->publishEmergencyViews($force);
        }

        // Publish all views
        $this->publishAllViews($force);
        $this->publishEmergencyViews($force);

        $this->displaySuccessMessage();

        return 0;
    }

    private function publishAllViews(bool $force): void
    {
        $this->info('📄 Publishing package views...');

        $this->call('vendor:publish', [
            '--provider' => 'MKWebDesign\\FilamentWatchdog\\FilamentWatchdogServiceProvider',
            '--tag' => 'filament-watchdog-views',
            '--force' => $force,
        ]);

        $this->info('✅ Package views published');
    }

    private function publishEmergencyViews(bool $force): int
    {
        $this->info('🚨 Publishing emergency maintenance views...');

        $sourceDir = __DIR__ . '/../../resources/views/errors';
        $targetDir = resource_path('views/errors');
        $emergencyView = 'emergency-lockdown.blade.php';

        // Ensure target directory exists
        if (!File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
            $this->info("📁 Created directory: {$targetDir}");
        }

        $sourcePath = $sourceDir . '/' . $emergencyView;
        $targetPath = $targetDir . '/' . $emergencyView;

        // Check if source exists
        if (!File::exists($sourcePath)) {
            $this->warn("⚠️  Source view not found, creating fallback emergency view...");
            return $this->createFallbackEmergencyView($targetPath, $force);
        }

        // Check if target exists and handle force option
        if (File::exists($targetPath) && !$force) {
            if (!$this->confirm("Emergency view already exists. Overwrite?")) {
                $this->warn("⚠️  Skipped: {$emergencyView}");
                return 0;
            }
        }

        // Copy the file
        try {
            File::copy($sourcePath, $targetPath);
            $this->info("✅ Published: {$emergencyView}");

            // Verify the file was created correctly
            if (File::exists($targetPath)) {
                $size = File::size($targetPath);
                $this->info("📊 File size: " . number_format($size) . " bytes");
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to publish emergency view: " . $e->getMessage());
            return 1;
        }
    }

    private function createFallbackEmergencyView(string $targetPath, bool $force): int
    {
        if (File::exists($targetPath) && !$force) {
            if (!$this->confirm("Emergency view already exists. Overwrite with fallback?")) {
                $this->warn("⚠️  Skipped fallback creation");
                return 0;
            }
        }

        try {
            $fallbackContent = $this->getFallbackEmergencyTemplate();
            File::put($targetPath, $fallbackContent);
            $this->info("✅ Created fallback emergency view");

            $size = File::size($targetPath);
            $this->info("📊 File size: " . number_format($size) . " bytes");

            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Failed to create fallback emergency view: " . $e->getMessage());
            return 1;
        }
    }

    private function showPreview(): int
    {
        $this->info('📋 Preview of files to be published:');
        $this->newLine();

        $files = [
            'Emergency View' => 'resources/views/errors/emergency-lockdown.blade.php',
            'Package Views' => 'resources/views/vendor/filament-watchdog/',
            'Config File' => 'config/filament-watchdog.php (use --tag=filament-watchdog-config)',
        ];

        foreach ($files as $type => $path) {
            $status = '📄';
            if (File::exists(base_path($path))) {
                $status = '✅ (exists)';
            }
            $this->line("  {$status} {$type}: {$path}");
        }

        $this->newLine();
        $this->info('💡 Use --force to overwrite existing files');
        $this->info('💡 Use --only-emergency to publish only the emergency view');

        return 0;
    }

    private function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->info('✅ All views published successfully!');
        $this->newLine();

        $this->info('📋 Published files:');
        $this->line('  - resources/views/errors/emergency-lockdown.blade.php');
        $this->line('  - resources/views/vendor/filament-watchdog/');

        $this->newLine();
        $this->info('🎨 Customization options:');
        $this->line('  - Edit emergency view: resources/views/errors/emergency-lockdown.blade.php');
        $this->line('  - Configure colors/text: config/filament-watchdog.php (emergency section)');
        $this->line('  - Publish config: php artisan vendor:publish --tag=filament-watchdog-config');

        $this->newLine();
        $this->info('🧪 Test emergency lockdown:');
        $this->line('  - php artisan watchdog:emergency-lockdown activate');
        $this->line('  - php artisan watchdog:emergency-lockdown deactivate');

        $this->newLine();
        $this->info('📧 Don\'t forget to configure admin email addresses in config/filament-watchdog.php!');
    }

    private function getFallbackEmergencyTemplate(): string
    {
        return '{{-- 
    FilamentWatchdog Emergency Lockdown Maintenance Page (Fallback)
    Auto-generated by: php artisan watchdog:publish-views
    
    Customize this view or publish the full template with:
    php artisan vendor:publish --tag=filament-watchdog-errors
--}}
<!DOCTYPE html>
<html lang="{{ str_replace(\'_\', \'-\', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚨 Emergency Security Lockdown - {{ config(\'app.name\', \'FilamentWatchdog\') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 20px;
        }
        .container {
            text-align: center;
            max-width: 700px;
            background: rgba(255, 255, 255, 0.1);
            padding: 50px 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .icon {
            font-size: 5rem;
            margin-bottom: 25px;
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
        .subtitle {
            font-size: 1.1rem;
            line-height: 1.6;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        .status {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 15px;
            margin: 30px 0;
            text-align: left;
        }
        .status h3 {
            color: #ffd700;
            margin-bottom: 15px;
            font-size: 1.3rem;
        }
        .status ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .status li {
            padding: 8px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .status li:last-child {
            border-bottom: none;
        }
        .admin-info {
            background: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            padding: 20px;
            border-radius: 12px;
            margin: 30px 0;
        }
        .admin-info h3 {
            color: #ffd700;
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #ffd700, #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        @media (max-width: 768px) {
            .container {
                padding: 30px 20px;
            }
            h1 {
                font-size: 2rem;
            }
            .icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚨</div>
        <h1>Emergency Security Lockdown</h1>
        <p class="subtitle">
            Our security system has temporarily restricted access to protect your data. 
            We are working to resolve this situation as quickly as possible.
        </p>

        <div class="status">
            <h3>🛡️ Security Measures Active</h3>
            <ul>
                <li>🔒 Site access restricted to authorized administrators</li>
                <li>🧹 All user sessions have been cleared</li>
                <li>💾 Emergency backup created and secured</li>
                <li>📧 Administrators have been notified</li>
                <li>🔍 Security analysis in progress</li>
            </ul>
        </div>

        <div class="admin-info">
            <h3>👨‍💻 Administrator Access</h3>
            <p>
                If you are a system administrator, check your email for the emergency access link 
                or use the secret key provided during lockdown activation.
            </p>
        </div>

        <div class="footer">
            <div class="logo">🐕 FilamentWatchdog</div>
            <p>Advanced Security Monitoring & Protection</p>
            <p><strong>Expected resolution:</strong> Within 1-2 hours</p>
            <p style="margin-top: 15px;">
                <strong>Lockdown activated:</strong> <span id="lockdown-time"></span>
            </p>
        </div>
    </div>

    <script>
        // Set lockdown time
        document.getElementById(\'lockdown-time\').textContent = new Date().toLocaleString();
        
        // Auto-refresh every 5 minutes to check if lockdown is lifted
        setTimeout(function() {
            window.location.reload();
        }, 300000);
    </script>
</body>
</html>';
    }
}