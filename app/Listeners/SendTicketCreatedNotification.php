<?php

namespace App\Listeners;

use App\Events\TicketCreated;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendTicketCreatedNotification
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
    public function handle(TicketCreated $event): void
    {
        $record = $event->order;
        $data = $event->ticketData;
        $type = $event->type;
        $subject = $record->team->name . ' – ' . $type . ' ' . $record->id;

        // Send support ticket
        $ticket = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic aVIzUUZBTDc1dURHY1ZHWkRpeDo=',
        ])
            ->post('https://nordiccharge.freshdesk.com/api/v2/tickets', [
                'email' => auth()->user()->email,
                'type' => $data['type'],
                'subject' => $subject,
                'description' => $data['message'],
                'priority' => (int)$data['priority'],
                'custom_fields' => ['cf_reference_number' => $record->id],
                'status' => 2,
                'source' => 2,
            ]);

        if ($ticket->status() == 201) {
            Log::info('Ticket created: ' . $ticket);
            Notification::make()
                ->title('Ticket created successfully')
                ->success()
                ->send();
        } else {
            Log::warning('Failed to create ticket: ' . $ticket);
            Notification::make()
                ->title('Failed to create ticket')
                ->warning()
                ->send();
        }
    }
}
