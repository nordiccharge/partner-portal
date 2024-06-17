<?php

namespace App\Filament\Operation\Resources\InvoiceResource\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class AmountDueOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Invoice amount due',
                Number::currency(Invoice::all()->where('status', '!=', InvoiceStatus::Sent)->sum('total_price'), 'DKK', 'de'))
        ];
    }
}
