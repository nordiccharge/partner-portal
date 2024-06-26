<?php

namespace App\Jobs;

use App\Models\Order;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MontaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $record;
    protected $subscription;
    protected $model;
    protected $user;
    public function __construct(Order $record, $subscription, $model, $user)
    {
        $this->record = $record;
        $this->subscription = $subscription;
        $this->model = $model;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('Creating on Monta...');
        $record = $this->record;
        $model = $this->model;
        $subscription = $this->subscription;
        $user = $this->user;
        $id = (string) $record->id;
        try {
            $response = Http::timeout(300)
                ->get('https://monta-script-obd7ro23jq-lz.a.run.app/create/nordisk-energi', [
                    'name' => $record->customer_first_name . ' ' . $record->customer_last_name,
                    'email' => $record->customer_email,
                    'address' => $record->shipping_address,
                    'zip' => $record->postal->postal,
                    'city' => $record->city->name,
                    'model' => $model,
                    'id' => $subscription,
                ]);
            if ($response->status() == 201) {
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Order created on Monta: ' . $response->body());
                Notification::make()
                    ->title("#{$id} : CREATED on Monta")
                    ->body($response->body())
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->sendToDatabase($user)
                    ->broadcast($user)
                    ->success()
                    ->send();
            } else {
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Failed to create order on Monta:' . $response->status() . ' ' . $response->body());
                Notification::make()
                    ->title("#{$id} : FAILED to create order on Monta")
                    ->body($response->body())
                    ->icon('heroicon-o-x-circle')
                    ->iconColor('danger')
                    ->sendToDatabase($user)
                    ->broadcast($user)
                    ->danger()
                    ->send();
            }
        } catch (Exception $e) {
            activity()
                ->performedOn($record)
                ->event('system')
                ->log('Failed to create order on Monta: ' . $e->getMessage());
            Notification::make()
                ->title("#{$id} : FAILED to create order on Monta")
                ->body($e->getMessage())
                ->icon('heroicon-o-x-circle')
                ->iconColor('danger')
                ->sendToDatabase($user)
                ->broadcast($user)
                ->danger()
                ->send();
        }
    }
}
