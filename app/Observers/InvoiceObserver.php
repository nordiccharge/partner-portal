<?php

namespace App\Observers;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\Log;

class InvoiceObserver
{
/**
     * Handle the Invoice "created" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function created(Invoice $invoice)
    {
        if ($invoice->status == InvoiceStatus::Paid) {
            return;
        }

        Log::debug('Invoice created: ' . $invoice->id);
        $total_price = 0;
        $order = $invoice->invoiceable;
        if ($order instanceof Order) {
            Log::debug('Invoiceable is OrderItem');
            foreach ($order->items as $item) {
                $product = Product::findOrFail($item->inventory->product_id);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'title' => $product->name . ' | ' . $product->sku,
                    'price' => $item->price,
                    'quantity' => $item->quantity
                ]);

                $total_price += $item->price * $item->quantity;
            }

            if ($order->installation_required) {
                Log::debug('Instalation required');
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'title' => $order->installation->kw . ' Instalation: ' . $order->installation->name,
                    'price' => $order->installation->price,
                    'quantity' => 1
                ]);

                $total_price += $order->installation->price;
            }

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'title' => 'Nordic Charge fee',
                'price' => $order->pipeline->nc_price,
                'quantity' => 1
            ]);

            $total_price += $order->pipeline->nc_price;
        } elseif ($order instanceOf PurchaseOrder) {
            Log::debug('Invoiceable is PurchaseOrder');
            foreach ($order->items as $item) {
                $product = Product::findOrFail($item->product_id);
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'title' => $product->name . ' | ' . $product->sku,
                    'price' => 0,
                    'quantity' => $item->quantity
                ]);
            }
        }

        if ($invoice->total_price != $total_price) {
            $invoice->update([
                'total_price' => $total_price
            ]);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function updated(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "restored" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function restored(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return void
     */
    public function forceDeleted(Invoice $invoice)
    {
        //
    }
}
