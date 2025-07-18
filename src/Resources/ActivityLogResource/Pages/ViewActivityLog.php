<?php

namespace MKWebDesign\FilamentWatchdog\Resources\ActivityLogResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use MKWebDesign\FilamentWatchdog\Resources\ActivityLogResource;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
