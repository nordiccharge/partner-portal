<?php

namespace App\Jobs;

use App\Enums\StageAutomation;
use App\Models\Order;
use App\Models\Stage;
use Carbon\Carbon;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ErrorHandler\Debug;

class MontaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $record;
    protected $subscription;
    protected $chargerName;
    protected $user;
    public function __construct(Order $record, $subscription, $chargerName, $user = null)
    {
        $this->record = $record;
        $this->subscription = $subscription;
        $this->chargerName = $chargerName;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug('Creating on Monta...');
        $record = $this->record;
        $subscription = $this->subscription;
        $user = $this->user;
        $chargerName = $this->chargerName;
        $id = (string) $record->id;
        try {
            Log::debug('Choosing operator...');
            $url = '';
            if ($record->team->id == 5) {
                Log::debug('Creating on Monta Strømlinet...');
                $url = 'https://monta-script-obd7ro23jq-lz.a.run.app/create/stromlinet';
            } elseif ($record->team->id == 7) {
                Log::debug('Creating on Monta Nordisk Energi...');
                $url = 'https://monta-script-obd7ro23jq-lz.a.run.app/create/nordisk-energi';
            } else {
                Log::error('Team not found');
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Failed to create order on Monta: Team not found');
                if ($user != null) {
                    Notification::make()
                        ->title("#{$id} : FAILED to create order on Monta")
                        ->body('Team not found')
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->sendToDatabase($user)
                        ->broadcast($user);
                }
                return;
            }
            $response = Http::timeout(300)
                ->get($url, [
                    'name' => $record->customer_first_name . ' ' . $record->customer_last_name,
                    'email' => $record->customer_email,
                    'address' => $record->shipping_address,
                    'zip' => $record->postal->postal,
                    'city' => $record->city->name,
                    'model' => $chargerName,
                    'id' => $subscription,
                ]);
            if ($response->status() == 201) {
                Log::debug('Successfully created Monta team, response: ' . $response->body());
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Order created on Monta: ' . $response->body());
                $record->update([
                    "action" => $response->json()['url']
                ]);
                Log::debug('Updating stage on Monta...');
                try {
                    $monta_stage = Stage::where('pipeline_id', (int)$record->pipeline_id)->where('automation_type', StageAutomation::Monta)->first();
                    $new_stage = $record->stage;
                    if ($record->stage->order <= $monta_stage->order) {
                        $new_stage = $monta_stage;
                    }
                    $record->update([
                        'stage_id' => $new_stage->id,
                    ]);
                } catch (Exception $e) {
                    Log::debug('Failed to update stage on Monta: ' . $e->getMessage());
                }
                if ($user != null) {
                    Notification::make()
                        ->title("#{$id} : CREATED on Monta")
                        ->body($response->body())
                        ->icon('heroicon-o-check-circle')
                        ->iconColor('success')
                        ->sendToDatabase($user)
                        ->broadcast($user);
                }
            } else {
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Failed to create order on Monta:' . $response->status() . ' ' . $response->body());
                if ($user != null) {
                    Notification::make()
                        ->title("#{$id} : FAILED to create order on Monta")
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->sendToDatabase($user)
                        ->broadcast($user);
                }
            }
        } catch (Exception $e) {
            activity()
                ->performedOn($record)
                ->event('system')
                ->log('Failed to create order on Monta: ' . $e->getMessage());
            if ($user != null) {
                Notification::make()
                    ->title("#{$id} : FAILED to create order on Monta")
                    ->body($e->getMessage())
                    ->icon('heroicon-o-x-circle')
                    ->iconColor('danger')
                    ->sendToDatabase($user)
                    ->broadcast($user);
            }
        }
    }
}
