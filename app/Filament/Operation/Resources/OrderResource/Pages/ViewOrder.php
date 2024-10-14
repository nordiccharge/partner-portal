<?php

namespace App\Filament\Operation\Resources\OrderResource\Pages;

use App\Events\PushOrderToShipping;
use App\Events\SendEmailToInstaller;
use App\Filament\Operation\Resources\OrderResource;
use App\Filament\Operation\Resources\ReturnOrderResource;
use App\Forms\Components\Flow;
use App\Jobs\InstallerJob;
use App\Jobs\MontaJob;
use App\Models\Charger;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Postal;
use App\Models\ReturnOrder;
use App\Models\Stage;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\View;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;
use Parallax\FilamentComments\Models\FilamentComment;
use SendGrid\Mail\Mail;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string|Htmlable
    {
        $order = $this->getRecord();
        return new HtmlString('<span class="text-gray-800 text-2xl dark:text-white">' . $order->team->name . '</span>  <span class="text-primary-600 font-medium text-2xl">#' . $order->id . '</span>');
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Order Details')
                    ->schema([
                        TextEntry::make('pipeline.name')
                            ->label('Pipeline')
                            ->copyable(),
                        TextEntry::make('id')
                            ->label('Order ID')
                            ->copyable(),
                        TextEntry::make('order_reference')
                            ->copyable(),
                        TextEntry::make('updated_at'),
                        TextEntry::make('created_at')
                    ])
                    ->icon('heroicon-m-information-circle')
                    ->columns(5)
                    ->columnSpanFull()
                    ->headerActions([
                        Action::make('monta_action')
                            ->label('Create on Monta')
                            ->icon('heroicon-o-cloud-arrow-up')
                            ->hidden(fn (Order $order) => ($order->chargers->count() <= 0))
                            ->modalHeading('Create on Monta?')
                            ->modalDescription('This will create team, invite customer and create chargepoint (with subscription) on Monta.')
                            ->form([
                                Select::make('charger')
                                    ->label('ChargePoint integration model')
                                    ->options(function (Order $order) {
                                        return $order->chargers->mapWithKeys(function ($item) {
                                            return [$item->id => $item->product->name . ' - ' . $item->serial_number];
                                        });
                                    })
                                    ->searchable()
                                    ->required(),
                                Select::make('subscription')
                                    ->label('Monta Subscription')
                                    ->options(function (Order $record) {
                                        if ($record->team->id == 5 ) {
                                            return [
                                                'false' => 'No subscription',
                                                '1864' => 'El. refusion abonnement til kunder som har købt en ladestander',
                                                '1878' => 'El. refusion - leje af ladestander',
                                            ];
                                        }

                                        if ($record->team->id == 7) {
                                            return                                         [
                                                'false' => 'No subscription',
                                                '2636' => '#2636 Elrefusion abonnement ink. moms – Nordisk Energi',
                                                '2757' => '#2757 Serviceaftale uden refusion – Nordisk Energi',
                                            ];
                                        }

                                        return [];
                                    })
                                    ->searchable()
                                    ->required(),

                            ])
                            ->modalSubmitActionLabel('Create now')
                            ->visible(fn (Order $record) => $record->team_id == 7 || 5)
                            ->action(function (Order $record, array $data) {
                                $charger = $record->chargers->where('id', $data['charger'])->first();
                                Log::debug('Charger brand is: ' . $charger->product->brand->name);
                                MontaJob::dispatch($record, $data['subscription'], $charger->product->brand->name, auth()->user())
                                    ->onQueue('monta-ne')
                                    ->onConnection('database')
                                    ->delay(Carbon::now()->addSeconds(10));
                                Notification::make()
                                    ->title('You are NOT done!')
                                    ->body('The system is starting the Monta job. Please check back and follow the proccess in notifications.')
                                    ->icon('heroicon-o-information-circle')
                                    ->iconColor('warning')
                                    ->send();
                            }),
                        Action::make("installer_action")
                            ->label("Add to Installer Tool")
                            ->color('gray')
                            ->icon("heroicon-o-user-plus")
                            ->modalHeading('Create on Installer Tool?')
                            ->modalDescription('This will create the charger on the installer tool.')
                            ->form([
                                Select::make('model')
                                    ->label('ChargePoint integration model')
                                    ->options(function (Order $order) {
                                        return $order->chargers->mapWithKeys(function ($item) {
                                            return [$item->id => $item->product->name . ' - ' . $item->serial_number];
                                        });
                                    })
                                    ->afterStateUpdated(fn ($state, callable $set) => $state != null ? $set('service', Charger::find($state)->where('id', $state)->first()->service) : $set('service', null))
                                    ->live()
                                    ->searchable()
                                    ->required(),
                                TextInput::make('service')
                                    ->label('Service URL (Monta Integration URL)')
                                    ->url()
                                    ->reactive()
                                    ->required()
                            ])
                            ->action(function (Order $record, array $data) {
                                $charger = $record->chargers->where('id', $data['model'])->first();
                                $charger->update([
                                    'service' => $data['service']
                                ]);
                                InstallerJob::dispatch($record, $data['model'], $data['service'], auth()->user())
                                    ->onQueue('monta-ne')
                                    ->onConnection('database')
                                    ->delay(Carbon::now()->addSeconds(10));
                            })
                            ->hidden(fn (Order $order) => ($order->chargers->count() <= 0)),
                        Action::make('Create invoice')
                            ->icon('heroicon-o-document-check')
                            ->color('info')
                            ->modalHeading('Create invoice for order')
                            ->modalDescription('Are you sure you want to create an invoice for order?')
                            ->form([
                                \Filament\Forms\Components\View::make('filament.forms.components.invoices'),
                            ])
                            ->requiresConfirmation()
                            ->action(function (Order $record) {
                                Invoice::create([
                                    'invoiceable_id' => $record->id,
                                    'invoiceable_type' => Order::class,
                                    'status' => 'pending'
                                ]);
                                Notification::make()
                                    ->title('Invoice created')
                                    ->success()
                                    ->send();
                                activity()
                                    ->performedOn($record)
                                    ->event('system')
                                    ->log('Invoice created by ' . auth()->user()->name);
                            }),
                    ]),
                Section::make('Customer Overview')
                    ->schema([
                        Group::make([
                            TextEntry::make('customer_first_name')
                                ->label('First name')
                                ->copyable(),
                            TextEntry::make('customer_last_name')
                                ->label('Last name')
                                ->copyable(),
                            TextEntry::make('customer_email')
                                ->label('Email')
                                ->copyable(),
                            TextEntry::make('customer_phone')
                                ->label('Phone')
                                ->copyable()
                        ])->columns(2),
                        Section::make('Shipping Details')
                            ->schema([
                                TextEntry::make('shipping_address')
                                    ->label('Adress')
                                    ->copyable(),
                                TextEntry::make('postal.postal')
                                    ->copyable(),
                                TextEntry::make('city.name')
                                    ->copyable(),
                                TextEntry::make('country.name')
                                    ->copyable(),
                        ])->columns(2),
                        Section::make([
                            TextEntry::make('note')
                                ->default('No note stated')
                        ])
                    ])
                ->columnSpanFull(),
                Section::make('Status')
                    ->schema([
                        ViewEntry::make('flow')
                            ->view('filament.forms.components.flow')
                            ->columnSpanFull(),
                        TextEntry::make('pipeline.name'),
                    ])
                    ->icon('heroicon-m-clipboard-document-list')
                    ->columnSpanFull(),
                Section::make('Shipping & Installation')
                    ->schema([
                        TextEntry::make('tracking_code')
                            ->default('Not available yet')
                            ->copyable(),
                        Group::make([
                            IconEntry::make('installation_required')
                                ->boolean()
                                ->label('Installation included'),
                            TextEntry::make('installation.name'),
                            TextEntry::make('installer.company.name'),
                            TextEntry::make('installation_date')
                                ->visible(fn (Order $order) => $order->installation_required)
                                ->default('Not available yet'),
                            TextEntry::make('installation.price')
                                ->label('Price')
                                ->suffix(' DKK')
                        ])->columns(5),
                    ])->columns(1)
                    ->icon('heroicon-m-truck')
                    ->headerActions([
                        Action::make('pushToShippingCompany')
                            ->icon('heroicon-o-truck')
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalHeading('Push Order to Shipping Company')
                            ->modalDescription('Are you sure you want to push this order to shipping?')
                            ->modalSubmitActionLabel('Push')
                            ->hidden(fn (Order $record) => $record->stage->state == 'return' || $record->stage->state == 'completed')
                            ->action(function (Order $record) {
                                PushOrderToShipping::dispatch($record);
                            }),
                        Action::make('Notify installer')
                            ->icon('heroicon-o-paper-airplane')
                            ->color('gray')
                            ->requiresConfirmation()
                            ->modalSubmitActionLabel('Send')
                            ->action(function (Order $record) {
                                SendEmailToInstaller::dispatch($record);
                            })
                            ->hidden(fn (Order $record) => $record->installation_required == 0 || $record->installer_id == null),
                        Action::make('Assign new installer')
                            ->modalHeading('Assign Installer')
                            ->icon('heroicon-o-user-plus')
                            ->hidden(fn (Order $record) => $record->stage->state == 'return' || $record->installation_required == 0 || $record->installer_id != null)
                            ->form([
                                \Filament\Forms\Components\Select::make('installer_id')
                                    ->options(
                                        function () {
                                            return
                                                \App\Models\Installer::join('companies', 'installers.company_id', '=', 'companies.id')
                                                    ->pluck('companies.name', 'installers.id');
                                        })
                                    ->label('Installer')
                                    ->default(fn (Order $record) => Postal::find($record->postal_id)->installer_id)
                                    ->required(),
                                Toggle::make('send_email')
                                    ->label('Send email to installer')
                                    ->default(true)
                            ])
                            ->modalWidth('sm')
                            ->modalSubmitActionLabel('Assign')
                            ->color('gray')
                            ->action(function (Order $record, array $data) {
                                $record->update(['installer_id' => $data['installer_id']]);
                                $order = $record;
                                $order_items = '';
                                foreach ($order->items as $item) {
                                    $order_items = $order_items . '<div style="width: 100%; text-align: right;"><p><strong>' . $item->quantity . 'x</strong> ' . $item->inventory->product->name . '</p></div>';
                                }
                                if ($data['send_email'] == 1) {
                                    SendEmailToInstaller::dispatch($order);
                                }
                            }),
                    ]),
                Section::make('Cart Details')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('inventory.product.name')
                                    ->label('Name'),
                                TextEntry::make('quantity'),
                                TextEntry::make('inventory.product.sku')
                                    ->label('SKU'),
                                TextEntry::make('price')
                                    ->suffix(' DKK')
                                    ->label('Price'),
                                ImageEntry::make('inventory.product.image_url')
                                    ->label('')
                            ])->columns(2)
                    ])
                    ->icon('heroicon-m-shopping-bag')
                    ->headerActions([
                        Action::make('Create return')
                            ->icon('heroicon-o-arrow-path')
                            ->hidden(fn (Order $record) => $record->stage->state == 'return')
                            ->color('warning')
                            ->requiresConfirmation()
                            ->form([
                                \Filament\Forms\Components\Textarea::make('reason')
                                    ->required()
                                    ->autofocus()
                                    ->placeholder('Specify a reason'),
                                \Filament\Forms\Components\Select::make('shipping_label')
                                    ->options([
                                        1 => 'Create Shipping Label Automatically',
                                        0 => 'No Label',
                                    ])
                                    ->required(),
                                \Filament\Forms\Components\View::make('filament.forms.components.invoices'),
                                Select::make('create_invoice')
                                    ->label('Do you want to create an invoice on this order?')
                                    ->options([
                                        1 => 'Yes',
                                        0 => 'No'
                                    ])
                                    ->required()
                            ])
                            ->action(function (Order $record, array $data) {
                                FilamentComment::create([
                                    'user_id' => auth()->user()->id,
                                    'subject_type' => 'App\Models\Order',
                                    'subject_id' => $record->id,
                                    'comment' => '<p><i>Return created:<br>' . $data['reason'] . '</i></p>',
                                ]);
                                $return_order = ReturnOrder::create([
                                    'team_id' => $record->team->id,
                                    'order_id' => $record->id,
                                    'pipeline_id' => $record->pipeline_id,
                                    'reason' => $data['reason'],
                                    'shipping_label' => $data['shipping_label'],
                                    'state' => 'pending'
                                ]);
                                $record->update(['pipeline_id' => 1, 'stage_id' => 1]);
                                /*if ($data['shipping_label'] == 1) {
                                    $return_order->update(['state' => 'processing']);
                                }*/

                                $this->redirect(ReturnOrderResource::getUrl());

                                if ($data['create_invoice'] == 1) {
                                    Invoice::create([
                                        'invoiceable_id' => $record->id,
                                        'invoiceable_type' => Order::class,
                                        'status' => 'pending'
                                    ]);
                                    Notification::make()
                                        ->title('Invoice created')
                                        ->success()
                                        ->send();
                                }
                            })
                            ->modalIcon('heroicon-o-arrow-path'),
                    ]),
                Section::make('Comments')
                    ->schema([
                        CommentsEntry::make('comments')

                    ])
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->columns(1),
            ])
            ->columns(1);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('History')
                ->icon('heroicon-o-document-text')
                ->link()
                ->color('white')
                ->visible(auth()->user()->isAdmin())
                ->url(fn ($record) => \App\Filament\Resources\OrderResource::getUrl('activities', ['record' => $record])),
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->label('Edit')
                ->color('white')
                ->link(),
            Actions\Action::make('Complete')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Complete order')
                ->modalDescription('Are you sure you want to complete this order?')
                ->form([
                    \Filament\Forms\Components\View::make('filament.forms.components.invoices'),
                    Select::make('create_invoice')
                        ->label('Do you want to create an invoice on this order?')
                        ->options([
                            1 => 'Yes',
                            0 => 'No'
                        ])
                        ->required()
                ])
                ->hidden(fn (Order $record) => $record->stage->state == 'return' || $record->stage->state == 'completed')
                ->action(function (array $data, Order $record) {
                    $record->update(['stage_id' => $record->pipeline->stages->where('state', 'completed')->first()->id]);
                    if ($data['create_invoice'] == 1) {
                        Invoice::create([
                            'invoiceable_id' => $record->id,
                            'invoiceable_type' => Order::class,
                            'status' => 'pending'
                        ]);
                        Notification::make()
                            ->title('Invoice created')
                            ->success()
                            ->send();
                    }
                    FilamentComment::create([
                        'user_id' => auth()->user()->id,
                        'subject_type' => 'App\Models\Order',
                        'subject_id' => $record->id,
                        'comment' => '<p><i>Order Completed</i></p>',
                    ]);
                    activity()
                        ->performedOn($record)
                        ->event('system')
                        ->log('Order completed by ' . auth()->user()->name);
                }),
        ];
    }
}
