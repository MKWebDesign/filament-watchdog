<?php

namespace MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use MKWebDesign\FilamentWatchdog\Resources\FileIntegrityResource;

class CreateFileIntegrityCheck extends CreateRecord
{
    protected static string $resource = FileIntegrityResource::class;
}
