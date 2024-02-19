<?php

namespace App\Listeners;

use App\Enums\StageAutomation;
use App\Events\OrderCreated;
use App\Models\Installer;
use App\Models\Pipeline;
use App\Models\Stage;
use Illuminate\Support\Facades\Log;
use SendGrid\Mail\Mail;

class SendInstallationEmail
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
        Log::debug('Order Shipping Event');
        $order = $event->order;
        Log::debug($order);
        Log::debug($order->installation_required . ' ' . $order->installation_id . ' ' . $order->team->sendgrid_auto_installer_allow);
        if ($order->installation_required &&
            $order->installation_id != null &&
            $order->team->sendgrid_auto_installer_allow
        ) {
            // Send email to installation team
            Log::debug('Contacting Installer Automatically');

            $order_items = '';

            if($order->items != null && $order->items != '' && count($order->items) > 0) {
                Log::debug($order->items);
                foreach ($order->items as $item) {
                    $order_items = $order_items . '<div style="width: 100%; text-align: right;"><p><strong>' . $item->quantity . 'x</strong> ' . $item->inventory->product->name . '</p></div>';
                }
            } else {
                Log::debug('No items in order');
            }
            if ($order->postal->installer_id != null) {
                Log::debug('Sending Installation Email to Installer');
                $email = new Mail();
                $email->setFrom('service@nordiccharge.com', 'Nordic Charge');
                $email->setTemplateId('d-537e166a52e845aaabcdbe9653c574ad');
                $email->addDynamicTemplateDatas([
                    'order_id' => $order->id,
                    'full_name' => $order->customer_first_name . ' ' . $order->customer_last_name,
                    'email' => $order->customer_email,
                    'phone' => $order->customer_phone,
                    'address' => $order->shipping_address,
                    'city' => $order->city->name,
                    'postal' => $order->postal->postal,
                    'country' => $order->country->short_name,
                    'kw' => $order->installation->kw,
                    'note' => $order->note,
                    'order_items' => $order_items
                ]);

                $email->setSubject('Ny installation til ' . $order->team->name);
                $installer = Installer::findOrFail($order->installer_id);
                $email->addTo($installer->contact_email);
                $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

                try {
                    $response = $sendgrid->send($email);
                    if ($response->statusCode() == 202) {
                        Log::debug('Email sent to installer');
                        $new_stage_id = Stage::where('pipeline_id', '=', $order->pipeline_id)
                            ->where('automation_type', '=', StageAutomation::InstallerContacted)
                            ->first()->id;
                        $order->update(['stage_id' => $new_stage_id]);
                        activity()
                            ->performedOn($order)
                            ->event('system')
                            ->log('Email sent to installer on ' . $installer->contact_email);
                    } else {
                        Log::error('Email not sent to installer: ' . $response->statusCode() . ' ' . $response->body());
                    }
                } catch (\Exception $e) {
                    Log::error('Caught exception on email' . $e->getMessage());
                }
            } else {
                    Log::debug('Sending Missing Installer Email to Nordic Charge');
                    $email = new Mail();
                    $email->setFrom('service@nordiccharge.com', 'Nordic Charge');
                    $email->setTemplateId('d-5a3f0d2221824c0a88bd0472cdb823fe');
                    $email->addDynamicTemplateDatas([
                        'order_id' => $order->id,
                        'full_name' => $order->customer_first_name . ' ' . $order->customer_last_name,
                        'email' => $order->customer_email,
                        'phone' => $order->customer_phone,
                        'address' => $order->shipping_address,
                        'city' => $order->city->name,
                        'postal' => $order->postal->postal,
                        'country' => $order->country->short_name,
                        'kw' => $order->installation->kw,
                        'note' => $order->note,
                        'order_items' => $order_items
                    ]);

                    $email->setSubject('MISSING INSTALLATION for ' . $order->team->sendgrid_name);
                    $email->addTo('service@nordiccharge.com');
                    $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

                    try {
                        $response = $sendgrid->send($email);
                        Log::debug($response->body());
                    } catch (\Exception $e) {
                        Log::error('Caught exception on email' . $e->getMessage());
                    }
            }

        }
    }
}
