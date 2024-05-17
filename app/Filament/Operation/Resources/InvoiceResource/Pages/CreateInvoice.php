<?php

namespace App\Filament\Operation\Resources\InvoiceResource\Pages;

use App\Filament\Operation\Resources\InvoiceResource;
use App\Filament\Operation\Resources\ReturnOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $this->redirect(ReturnOrderResource::getUrl());
        return parent::handleRecordCreation($data);
    }
}
