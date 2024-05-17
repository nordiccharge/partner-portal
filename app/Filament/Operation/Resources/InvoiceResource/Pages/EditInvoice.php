<?php

namespace App\Filament\Operation\Resources\InvoiceResource\Pages;

use App\Filament\Operation\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
