<?php


namespace MKWebDesign\FilamentWatchdog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Concerns\EvaluatesClosures;
use MKWebDesign\FilamentWatchdog\Pages\SecurityDashboard;
use MKWebDesign\FilamentWatchdog\Pages\ThreatTimeline;
use MKWebDesign\FilamentWatchdog\Pages\ForensicAnalysis;
use MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource;
use MKWebDesign\FilamentWatchdog\Resources\MalwareDetectionResource;
use MKWebDesign\FilamentWatchdog\Resources\ActivityLogResource;
use MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource;
use MKWebDesign\FilamentWatchdog\Widgets\SecurityOverviewWidget;
use MKWebDesign\FilamentWatchdog\Widgets\ThreatLevelWidget;
use MKWebDesign\FilamentWatchdog\Widgets\RecentAlertsWidget;

class FilamentWatchdogPlugin implements Plugin
{
    use EvaluatesClosures;

    public function getId(): string
    {
        return 'filament-watchdog';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->pages([
                SecurityDashboard::class,
                ThreatTimeline::class,
                ForensicAnalysis::class,
            ])
            ->resources([
                FileIntegrityResource::class,
                MalwareDetectionResource::class,
                ActivityLogResource::class,
                SecurityAlertResource::class,
            ])
            ->widgets([
                SecurityOverviewWidget::class,
                ThreatLevelWidget::class,
                RecentAlertsWidget::class,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public static function make(): static
    {
        return app(static::class);
    }
}