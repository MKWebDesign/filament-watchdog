<?php

namespace MKWebDesign\FilamentWatchdog\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CreateBaselineCommand extends Command
{
    protected $signature = 'watchdog:baseline';
    protected $description = 'Create baseline for file integrity monitoring';

    public function handle(): int
    {
        $this->info('🔄 Creating FilamentWatchdog baseline...');

        try {
            // Call the scan command with baseline option
            Artisan::call('watchdog:scan', ['--baseline' => true]);

            $this->info('✅ Baseline created successfully.');
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Baseline creation failed: ' . $e->getMessage());
            return 1;
        }
    }
}
