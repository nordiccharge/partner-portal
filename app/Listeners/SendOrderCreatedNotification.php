<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use League\Uri\Http;

class SendOrderCreatedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $team = $order->team();
        $allow_shipping = $team->get('shipping_api_send')->first()->shipping_api_send;
        $pipeline = $order->pipeline()->get('id')->first()->id;

        if ($allow_shipping == 1 && $pipeline == 1) {
            $items = array();
            foreach ($order->items()->get(['inventory_id', 'quantity']) as $item) {
                $inventory_item = Inventory::find($item['inventory_id']);
                $sku = Product::find($inventory_item['product_id'])->sku;
                $qty = $item['quantity'];
                array_push($items, ['sku' => $sku, 'qty' => $qty]);
            }

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-SmartPack-AppId' => '862b1f30-fa0c-4994-be27-7c3b19575024',
            'X-SmartPack-AccessToken' => 'H3xeH9pIrcJJy3R68RQx83jN1MwIK6KBhWCn6UccFDYtQs7yZ5GuW8FM/EVY2/cBcl0xJeYCRndmYoPtlMxZnQ=='
        ])->post('https://muramura.smartpack.dk/api/v1/order/create', [
            'orderNo' => $order->id,
            'referenceNo' => $order->order_reference,
            'sender' => [
                'name' => 'Nordic Charge ApS',
                'attention' => $team->get('name')->first()->name,
                'street1' => 'Kantatevej 30',
                'zipcode' => '2730',
                'city' => 'Herlev',
                'country' => 'DK',
                'phone' => '+4531435950',
                'email' => 'dk@nordiccharge.com'
            ],
            'recipient' => [
                'name' => $order->customer_first_name . ' ' . $order->customer_last_name,
                'street1' => $order->shipping_address,
                'zipcode' => $order->postal->postal,
                'city' => $order->city->name,
                'country' => $order->country->short_name,
                'phone' => $order->customer_phone,
                'email' => $order->customer_email
            ],
            'deliveryMethod' => 'gls_private_delivery',
            'items' => $items
        ]);

        }
    }
}
