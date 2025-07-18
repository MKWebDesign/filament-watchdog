<?php

namespace MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource;

class ListSecurityAlerts extends ListRecords
{
    protected static string $resource = SecurityAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
