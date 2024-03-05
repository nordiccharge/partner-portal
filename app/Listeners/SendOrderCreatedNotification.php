<?php

namespace App\Listeners;

use App\Enums\PipelineAutomation;
use App\Events\OrderCreated;
use App\Models\Company;
use App\Models\Installer;
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
    public function handle($event): void
    {
        $this->shippingNotification($event->order);
    }

    private function shippingNotification(Order $order): void {
        $team = $order->team();
        $allow_shipping = $team->get('shipping_api_send')->first()->shipping_api_send;
        $pipeline_id = $order->pipeline()->get('id')->first()->id;
        $pipeline_automation = $order->pipeline()->get('automation_type')->first()->automation_type;

        Log::debug('Starting Shipping Automation');

        if ($allow_shipping == 1 && $pipeline_automation == PipelineAutomation::Shipping) {
            Log::debug('Pipeline automation is set to shipping');
            $items = array();
            foreach ($order->items()->get(['inventory_id', 'quantity']) as $item) {
                $inventory_item = Inventory::find($item['inventory_id']);
                $sku = Product::find($inventory_item['product_id'])->sku;
                $qty = $item['quantity'];
                array_push($items, ['sku' => $sku, 'qty' => $qty]);
            }

            Log::debug('Items: ' . json_encode($items));
            $company = Company::findOrFail($team->get('company_id')->first()->company_id);
            Log::debug('Company: ' . $company->name);
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-SmartPack-AppId' => '862b1f30-fa0c-4994-be27-7c3b19575024',
                'X-SmartPack-AccessToken' => 'H3xeH9pIrcJJy3R68RQx83jN1MwIK6KBhWCn6UccFDYtQs7yZ5GuW8FM/EVY2/cBcl0xJeYCRndmYoPtlMxZnQ=='
            ])->post('https://muramura.smartpack.dk/api/v1/order/create', [
                'orderNo' => $order->id,
                'referenceNo' => $order->order_reference,
                'sender' => $this->getSender($company),
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

            Log::debug('Shipping Automation Response: ' . $response->status() . ' ' . $response->body());
            if ($response->status() == 200) {
                activity()
                    ->performedOn($order)
                    ->event('system')
                    ->log('Order sent to MuraMura');
            } else {
                activity()
                    ->performedOn($order)
                    ->event('system')
                    ->log('Failed to send order to MuraMura Response:' . $response->status() . ' ' . $response->body());

                $email = new Mail();
                $email->setFrom('service@nordiccharge.com', 'Nordic Charge');
                $email->setTemplateId('d-1020ffb3e4124400ac41653e3c4cc6bc');
                $email->addDynamicTemplateDatas([
                    'order_id' => $order->id,
                    'error_message' => 'Failed to send order to MuraMura Response:' . $response->status() . ' ' . $response->body(),
                ]);
                $email->setSubject('ERROR on ' . $order->id);
                $email->addTo('service@nordiccharge.com');
                $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
                $response = $sendgrid->send($email);
            }
        }
    }

    private function getSender(Company $company) {
        Log::debug('Getting Sender on ' . $company->name . ' ' . $company->companyType->name);
        if ($company->companyType->name == 'Customer') {
            return [
                'name' => $company->sender_name,
                'attention' => $company->sender_attention,
                'street1' => $company->sender_address,
                'street2' => $company->sender_address2,
                'zipcode' => $company->sender_zip,
                'city' => $company->sender_city,
                'country' => $company->sender_country,
                'phone' => $company->sender_phone,
                'email' => $company->sender_email
            ];
        }

        return [
            'name' => 'Nordic Charge ApS',
            'attention' => '',
            'street1' => 'Kantatevej 30',
            'street2' => '',
            'zipcode' => '2730',
            'city' => 'Herlev',
            'country' => 'DK',
            'phone' => '+45 31 43 59 50',
            'email' => 'sales@nordiccharge.com'
        ];
    }
}
