<?php

namespace App\Filament\Admin\Resources\CompanyResource\Pages;

use App\Filament\Admin\Resources\CompanyResource;
use App\Models\CompanyType;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCompanies extends ListRecords
{
    protected static string $resource = CompanyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Companies'),
            'partners' => Tab::make('Partners')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('company_type_id', CompanyType::where('name', 'Partner')->first()->id);
                }),
            'installers' => Tab::make('Installers')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->where('company_type_id', CompanyType::where('name', 'Installer')->first()->id);
                })
        ];
    }
}
