<?php

namespace MKWebDesign\FilamentWatchdog\Pages;

use Filament\Pages\Page;
use MKWebDesign\FilamentWatchdog\Traits\ConfiguresWatchdogNavigation;

class ThreatTimeline extends Page
{
    use ConfiguresWatchdogNavigation;

    protected static function getNavigationVisibility(): string
    {
        return 'conditional'; // Altijd zichtbaar op alle pagina's
    }
    protected static function getDefaultSecuritySort(): int
    {
        return 2;
    }

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament-watchdog::pages.threat-timeline';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Threat Timeline';
    protected static ?string $slug = 'security/threat-timeline';
}
