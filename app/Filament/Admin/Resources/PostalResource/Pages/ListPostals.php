<?php

namespace App\Filament\Admin\Resources\PostalResource\Pages;

use App\Filament\Admin\Resources\PostalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPostals extends ListRecords
{
    protected static string $resource = PostalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
