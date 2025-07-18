<?php

namespace MKWebDesign\FilamentWatchdog\Pages;

use Filament\Pages\Page;

class ForensicAnalysis extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static string $view = 'filament-watchdog::pages.forensic-analysis';
    protected static ?string $navigationGroup = 'Security';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Forensic Analysis';
    protected static ?string $slug = 'security/forensic-analysis';
}
