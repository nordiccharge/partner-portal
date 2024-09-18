<?php

namespace App\Jobs;

use App\Enums\StageAutomation;
use App\Models\Order;
use App\Models\Stage;
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

class InstallerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */

    protected $record;
    protected $monta;
    protected $model;
    protected $user;
    public function __construct(Order $record, $model, $monta, $user = null)
    {
        $this->record = $record;
        $this->model = $model;
        $this->monta = $monta;
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
        $monta = $this->monta;
        $user = $this->user;
        try {
            $charger = $record->chargers->where('id', $model)->first();
            if(!$charger->exists()) {
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Failed to create on Installer Tool: Charger not found');
                if ($user != null) {
                    Notification::make()
                        ->title('Error')
                        ->body('Charger not found')
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->send()
                        ->sendToDatabase($user)
                        ->broadcast($user);
                }
                return;
            }
            $guide = null;
            if ($charger->product->brand->name === 'Easee') {
                $guide = 1;
            } elseif ($charger->product->brand->name === 'NexBlue') {
                $guide = 2;
            } elseif ($charger->product->brand->name === 'Zaptec') {
                $guide = 3;
            } else {
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Failed to create on Installer Tool: Brand not supported');
                if ($user != null) {
                    Notification::make()
                        ->title('Error')
                        ->body('Brand not supported')
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->send()
                        ->sendToDatabase($user)
                        ->broadcast($user);
                }
                Log::error('Brand not supported');
                return;
            }
            $response = Http::post("https://installer-api.nordiccharge.com/chargers", [
                'serial_number' => $charger->serial_number,
                'guide' => $guide,
                'data' => json_encode([
                    'title' => $charger->product->name,
                    'image' => 'https://portal.nordiccharge.com/storage/products/' . $charger->product->image_url,
                    'monta_url' => $monta
                ])
            ]);
            if (!$response->status() == 201) {
                Log::error('Failed to create on Installer Tool: ' . $response->status() . ' ' . $response->body());
                if ($user != null) {
                    Notification::make()
                        ->title('Error')
                        ->body('Failed to create on Installer Tool: ' . $response->status())
                        ->icon('heroicon-o-x-circle')
                        ->iconColor('danger')
                        ->send()
                        ->sendToDatabase($user)
                        ->broadcast($user);
                }
                activity()
                    ->performedOn($record)
                    ->event('system')
                    ->log('Failed to create on Installer Tool: ' . $response->status());
                return;
            }
            if ($user != null) {
                Notification::make()
                    ->title('Success')
                    ->body('Created on Installer Tool')
                    ->icon('heroicon-o-check-circle')
                    ->iconColor('success')
                    ->send()
                    ->sendToDatabase($user)
                    ->broadcast($user);
            }
            activity()
                ->performedOn($record)
                ->event('system')
                ->log('Created on Installer Tool: ' . $response->body());
        } catch (\Exception $e) {
            activity()
                ->performedOn($record)
                ->event('system')
                ->log('Failed to create on Installer Tool: ' . $e->getMessage());
            Log::error('Failed to create on Installer Tool: ' . $e->getMessage());
        }
    }
}
