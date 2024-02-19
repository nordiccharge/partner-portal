<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource;
use App\Models\Inventory;
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

        ];
    }

    public function boot(): void {
        InventoryResource::scopeToTenant(false);
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'company' => Tab::make(Filament::getTenant()->name)
                ->modifyQueryUsing(function (Builder $query) {
                    $query
                        ->where('team_id', '=', Filament::getTenant()->id)
                        ->where('global', '=', 0);
                }),
            'global' => Tab::make('Nordic Charge')
                ->modifyQueryUsing(function (Builder $query) {
                    $query
                        ->where('global', '=', 1);
                })
        ];
    }
}
