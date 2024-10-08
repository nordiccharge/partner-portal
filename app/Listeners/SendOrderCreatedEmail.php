<?php

namespace App\Listeners;

use App\Enums\StageAutomation;
use App\Events\OrderCreated;
use App\Models\Order;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use SendGrid\Mail\Mail;

class SendOrderCreatedEmail
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
        Log::debug('Order Created Event');
        if ($order->team->allow_sendgrid &&
            $order->team->sendgrid_order_created_allow &&
            $order->team->sendgrid_order_created_id != '' &&
            $order->team->sendgrid_order_created_id != null &&
            $order->team->sendgrid_email != '' &&
            $order->team->sendgrid_email != null &&
            $order->team->sendgrid_name != '' &&
            $order->team->sendgrid_name != null &&
            $order->team->sendgrid_url != '' &&
            $order->team->sendgrid_url != null
            ) {
            Log::debug('SendGrid Order Created is allowed by settings');
            if ($order->stage->automation_type == StageAutomation::Created) {
                Log::debug('Order Created Event in Stage – proceeding');
            } else {
                Log::debug('Missing Order Created Event in Stage');
                return;
            }
        } else {
            Log::error('SendGrid Order Created is not allowed by settings');
            return;
        }

        $order_items = '';

        if($order->items != null && $order->items != '' && count($order->items) > 0) {
            Log::debug($order->items);
            foreach ($order->items as $item) {
                $order_items = $order_items . '<div style="width: 100%; text-align: right;"><p><strong>' . $item->quantity . 'x</strong> ' . $item->inventory->product->name . '</p></div>';
            }
        } else {
            Log::debug('No items in order');
        }

        Log::debug('Sending Order Created Email');
        $email = new Mail();
        $email->setFrom($order->team->sendgrid_email, $order->team->sendgrid_name);
        $email->setTemplateId($order->team->sendgrid_order_created_id);
        $email->addDynamicTemplateDatas([
            'order_number' => $order->id,
            'first_name' => $order->customer_first_name,
            'full_name' => $order->customer_first_name . ' ' . $order->customer_last_name,
            'address' => $order->shipping_address,
            'city' => $order->city->name,
            'postal' => $order->postal->postal,
            'country' => $order->country->short_name,
            'url' => $order->team->sendgrid_url,
            'order_items' => $order_items
        ]);
        $email->setSubject('Ordrebekræftelse #' . $order->id . ' fra ' . $order->team->sendgrid_name);
        $email->addTo($order->customer_email, $order->customer_first_name . ' ' . $order->customer_last_name);
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        try {
            $response = $sendgrid->send($email);
            Log::debug($response->body());
            activity()
                ->performedOn($order)
                ->event('system')
                ->log('Email sent to customer on ' . $order->customer_email);
        } catch (\Exception $e) {
            Log::error('Caught exception on emailNotification()' . $e->getMessage());
        }
    }
}
