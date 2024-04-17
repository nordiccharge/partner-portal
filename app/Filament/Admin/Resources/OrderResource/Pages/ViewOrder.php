<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Events\PushOrderToShipping;
use App\Events\SendEmailToInstaller;
use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\ReturnOrderResource;
use App\Forms\Components\Flow;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\ReturnOrder;
use App\Models\Stage;
use Filament\Actions;
use Filament\Forms\Components\Toggle;
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
use Illuminate\Support\Facades\Log;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;
use Parallax\FilamentComments\Models\FilamentComment;
use SendGrid\Mail\Mail;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Status')
                    ->schema([
                        ViewEntry::make('flow')
                            ->view('filament.forms.components.flow')
                            ->columnSpanFull(),
                    ])
                    ->icon('heroicon-m-clipboard-document-list'),
                Section::make('Overview test')
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
                            Section::make([
                                TextEntry::make('note')
                                    ->default('No note stated')
                            ])
                        ])->columns(4),
                    ]),
                Section::make('Order Details')
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
                Section::make('Shipping & Installation')
                    ->schema([
                        TextEntry::make('tracking_code')
                            ->default('Not available yet')
                            ->copyable(),
                        Group::make([
                            IconEntry::make('installation_required')
                                ->boolean()
                                ->label('Installation included'),
                            TextEntry::make('installer.company.name'),
                            TextEntry::make('installation_date')
                                ->visible(fn (Order $order) => $order->installation_required)
                                ->default('Not available yet'),
                            TextEntry::make('installation.price')
                                ->label('Price')
                                ->suffix(' DKK')
                        ])->columns(4),
                    ])->columns(1)
                    ->icon('heroicon-m-truck'),
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
                    ->icon('heroicon-m-shopping-bag'),
                Section::make('Comments')
                    ->schema([
                        CommentsEntry::make('comments')

                    ])
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->columns(1),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Push')
                ->icon('heroicon-o-truck')
                ->link()
                ->requiresConfirmation()
                ->modalHeading('Push Order to Shipping')
                ->modalDescription('Are you sure you want to push this order to shipping?')
                ->modalSubmitActionLabel('Push')
                ->hidden(fn (Order $record) => $record->stage->state == 'return' || $record->stage->state == 'completed')
                ->action(function (Order $record) {
                    PushOrderToShipping::dispatch($record);
                }),
            Actions\Action::make('Notify Installer')
                ->icon('heroicon-o-paper-airplane')
                ->link()
                ->requiresConfirmation()
                ->modalSubmitActionLabel('Send')
                ->action(function (Order $record) {
                    SendEmailToInstaller::dispatch($record);
                })
                ->hidden(fn (Order $record) => $record->installation_required == 0 || $record->installer_id == null),
            Actions\Action::make('Installer')
                ->modalHeading('Assign Installer')
                ->icon('heroicon-o-user-plus')
                ->link()
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
                        ->required(),
                    Toggle::make('send_email')
                        ->label('Send email to installer')
                        ->default(true)
                ])
                ->modalWidth('sm')
                ->modalSubmitActionLabel('Assign')
                ->color('primary')
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
            Actions\Action::make('Return')
                ->icon('heroicon-o-arrow-path')
                ->link()
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
                })
                ->modalIcon('heroicon-o-arrow-path'),
            Actions\EditAction::make()
                ->icon('heroicon-o-pencil-square')
                ->label('Edit')
                ->link(),
            Actions\Action::make('Complete')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Complete order & create invoice')
                ->modalDescription('Are you sure you want to complete this order?')
                ->hidden(fn (Order $record) => $record->stage->state == 'return' || $record->stage->state == 'completed')
                ->action(function (Order $record) {
                    $record->update(['stage_id' => $record->pipeline->stages->where('state', 'completed')->first()->id]);
                    Invoice::create([
                        'invoiceable_id' => $record->id,
                        'invoiceable_type' => Order::class,
                        'status' => 'pending'
                    ]);
                    Notification::make()
                        ->title('Invoice created')
                        ->success()
                        ->send();
                }),
        ];
    }
}
