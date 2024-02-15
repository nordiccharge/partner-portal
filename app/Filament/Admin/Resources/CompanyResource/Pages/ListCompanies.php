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
        $tabs = ['all' => Tab::make('All Companies')];
        foreach (CompanyType::all() as $companyType) {
            $tabs[strtolower($companyType->name)] = Tab::make($companyType->name)
                ->modifyQueryUsing(function (Builder $query) use ($companyType) {
                    $query->where('company_type_id', $companyType->id);
                });
        }
        return $tabs;
    }
}
