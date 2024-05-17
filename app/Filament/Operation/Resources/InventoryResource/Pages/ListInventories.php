<?php

namespace App\Filament\Operation\Resources\InventoryResource\Pages;

use App\Filament\Operation\Resources\InventoryResource;
use Filament\Actions;
use Filament\Facades\Filament;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'company' => Tab::make('Company')
                ->modifyQueryUsing(function (Builder $query) {
                    $query
                        ->where('global', '=', 0);
                }),
            'global' => Tab::make('Global')
                ->modifyQueryUsing(function (Builder $query) {
                    $query
                        ->where('global', '=', 1);
                })
        ];
    }
}
