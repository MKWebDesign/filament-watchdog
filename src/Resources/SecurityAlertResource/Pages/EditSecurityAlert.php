<?php

namespace MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MKWebDesign\FilamentWatchdog\Resources\SecurityAlertResource;

class EditSecurityAlert extends EditRecord
{
    protected static string $resource = SecurityAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
