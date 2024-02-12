<?php

namespace App\Observers;

use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Log;

class InvoiceItemObserver
{
    public function updated(InvoiceItem $invoiceItem)
    {
        $invoice = $invoiceItem->invoice;
        $total_price = 0;
        foreach ($invoice->items as $item) {
            $total_price += $item->price * $item->quantity;
        }
        Log::debug($total_price);
        if ($invoice->total_price != $total_price) {
            $invoice->update([
                'total_price' => $total_price
            ]);
        }
    }
}
