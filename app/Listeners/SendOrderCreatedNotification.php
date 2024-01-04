<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use League\Uri\Http;
use SendGrid\Mail\Mail;

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
        $this->shippingNotification($event->order);
    }

    private function shippingNotification(Order $order): void {
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
                    'name' => $team->company()->get('sender_name')->first()->sender_name,
                    'attention' => $team->company()->get('sender_attention')->first()->sender_attention,
                    'street1' => $team->company()->get('sender_address')->first()->sender_address,
                    'street2' => $team->company()->get('sender_address2')->first()->sender_address2,
                    'zipcode' => $team->company()->get('sender_zip')->first()->sender_zip,
                    'city' => $team->company()->get('sender_city')->first()->sender_city,
                    'country' => $team->company()->get('sender_country')->first()->sender_country,
                    'phone' => $team->company()->get('sender_phone')->first()->sender_phone,
                    'email' => $team->company()->get('sender_email')->first()->sender_email
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
                'deliveryMethod' => $order->pipeline()->get('shipping_type')->first()->shipping_type,
                'items' => $items
            ]);

        }
    }
}
