<?php

namespace MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource;

class ViewSecurityAlert extends ViewRecord
{
    protected static string $resource = SecurityAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
