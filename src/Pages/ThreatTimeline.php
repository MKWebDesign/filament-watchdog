<?php

namespace MKWebDesign\FilamentWatchdog\Pages;

use Filament\Pages\Page;

class ThreatTimeline extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static string $view = 'filament-watchdog::pages.threat-timeline';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Threat Timeline';
    protected static ?string $slug = 'security/threat-timeline';
}
