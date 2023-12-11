<?php

namespace App\Filament\Admin\Resources\PipelineResource\Pages;

use App\Filament\Admin\Resources\PipelineResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPipeline extends EditRecord
{
    protected static string $resource = PipelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
