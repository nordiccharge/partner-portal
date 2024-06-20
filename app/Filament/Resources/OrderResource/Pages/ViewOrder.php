<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Events\TicketCreated;
use App\Filament\Resources\OrderResource;
use App\Models\Inventory;
use App\Models\Order;
use Faker\Provider\Text;
use Filament\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use http\Env\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Nette\Utils\Html;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Status')
                    ->schema([
                        ViewEntry::make('flow')
                            ->view('filament.forms.components.flow')
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-m-clipboard-document-list'),
                \Filament\Infolists\Components\Section::make('Overview')
                    ->schema([
                        Group::make([
                            TextEntry::make('customer_first_name')
                                ->copyable(),
                            TextEntry::make('customer_last_name')
                                ->copyable(),
                            TextEntry::make('customer_email')
                                ->copyable(),
                            TextEntry::make('customer_phone')
                                ->copyable()
                        ])->columns(4),
                        Group::make([
                            TextEntry::make('shipping_address'),
                            TextEntry::make('postal.postal'),
                            TextEntry::make('city.name'),
                            TextEntry::make('country.name'),
                            \Filament\Infolists\Components\Section::make([
                                TextEntry::make('note')
                                    ->default('No note stated')
                            ])
                        ])->columns(4),
                    ]),
                \Filament\Infolists\Components\Section::make('Order Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID')
                            ->copyable(),
                        TextEntry::make('order_reference')
                            ->copyable(),
                        TextEntry::make('updated_at'),
                        TextEntry::make('created_at')
                    ])
                    ->icon('heroicon-m-information-circle')
                    ->columns(4),
                \Filament\Infolists\Components\Section::make('Shipping & Installation')
                    ->schema([
                        TextEntry::make('tracking_code')
                            ->copyable(),
                        IconEntry::make('installation_required')
                            ->boolean()
                            ->label('Installation included'),
                        TextEntry::make('installation.name'),
                        TextEntry::make('installation_date')
                            ->visible(fn (Order $order) => $order->installation_required)
                            ->default('Not available yet'),
                    ])->columns(4)
                    ->icon('heroicon-m-truck'),
                \Filament\Infolists\Components\Section::make('Cart Details')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('inventory.product.name')
                                    ->label('Name'),
                                TextEntry::make('quantity'),
                                TextEntry::make('inventory.product.sku')
                                    ->label('SKU'),
                                ImageEntry::make('inventory.product.image_url')
                                    ->label('')
                            ])->columns(2)
                    ])
                    ->icon('heroicon-m-shopping-bag'),
                \Filament\Infolists\Components\Section::make('Comments')
                    ->schema([
                        CommentsEntry::make('comments')

                    ])
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->columns(1),
            ]);
    }

    public function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Return')
                ->hidden() // Hide the action because it's not ready yet
                ->link()
                ->modalWidth('lg')
                ->form(fn (Order $order) => [
                    TextInput::make('order_url')
                        ->label('URL')
                        ->hint(new HtmlString('<br>This is the URL that the customer will use to return the order.'))
                        ->autofocus()
                        ->helperText('The return will be confirmed/declined by Nordic Charge after survey fulfillment.')
                        ->default(url('/return/' . $order->id))
                        ->readOnly()
                        ->extraAttributes(function ($state) {
                            return [
                                'x-on:click' => 'window.navigator.clipboard.writeText("'.$state.'"); $tooltip("Copied to clipboard", { timeout: 1500 });',
                            ];
                        })
                        ->suffixAction(
                            Action::make('copy')
                                ->icon('heroicon-o-clipboard')
                                ->action(function ($livewire, $state) {
                                    $livewire->js("document.getElementById('mountedActionsData.0.order_url').select(); document.execCommand('copy');");
                                    Notification::make()
                                        ->title('Saved to clipboard')
                                        ->success()
                                        ->send();
                            })
                        ),
                ])
                ->modalSubmitAction(false)
                ->modalCancelAction(false),
            Actions\Action::make('Support')
                ->color('primary')
                ->icon('heroicon-o-question-mark-circle')
                ->modalIcon('heroicon-o-question-mark-circle')
                ->modalHeading('Create Support Ticket')
                ->modalDescription('Please provide a detailed description of your issue or question')
                ->modalSubmitActionLabel('Create Ticket')
                ->modalWidth('xl')
                ->form([
                    Select::make('type')
                        ->label('What type of ticket is this?')
                        ->options([
                            'Delivery' => 'Delivery',
                            'Installation' => 'Installation',
                            'Technical Support' => 'Technical Support',
                            'Return' => 'Return',
                            'Other' => 'Other'
                        ])
                        ->required()
                        ->placeholder('Select a type'),
                    Select::make('priority')
                        ->options([
                            1 => 'Low',
                            2 => 'Medium',
                            3 => 'High',
                            4 => 'Urgent',
                        ])
                        ->required()
                        ->placeholder('Select a type'),
                    RichEditor::make('message')
                        ->required(),
                ])
                ->action( function (Order $record, array $data) {
                    TicketCreated::dispatch($record, $data, 'Order');
                })
        ];
    }

}
