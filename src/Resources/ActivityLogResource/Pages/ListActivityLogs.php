<?php

namespace MKWebDesign\FilamentWatchdog\Resources\ActivityLogResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use MKWebDesign\FilamentWatchdog\Resources\ActivityLogResource;

class ListActivityLogs extends ListRecords
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
