<?php

namespace MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource;

class EditFileIntegrityCheck extends EditRecord
{
    protected static string $resource = FileIntegrityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
