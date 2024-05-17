<?php

namespace App\Filament\Operation\Resources\InstallerJobResource\Pages;

use App\Filament\Operation\Resources\InstallerJobResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInstallerJob extends EditRecord
{
    protected static string $resource = InstallerJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
