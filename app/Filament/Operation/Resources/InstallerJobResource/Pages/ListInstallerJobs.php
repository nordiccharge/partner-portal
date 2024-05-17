<?php

namespace App\Filament\Operation\Resources\InstallerJobResource\Pages;

use App\Filament\Operation\Resources\InstallerJobResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInstallerJobs extends ListRecords
{
    protected static string $resource = InstallerJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
