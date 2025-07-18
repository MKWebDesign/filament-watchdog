<?php

namespace MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource;

class ListFileIntegrityChecks extends ListRecords
{
    protected static string $resource = FileIntegrityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
