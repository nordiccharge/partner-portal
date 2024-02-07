<?php

namespace App\Filament\Admin\Resources\CompanyTypeResource\Pages;

use App\Filament\Admin\Resources\CompanyTypeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListCompanyTypes extends ListRecords
{
    protected static string $resource = CompanyTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

}
