<?php

namespace App\Filament\Admin\Resources\OrderResource\Pages;

use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Admin\Resources\ReturnOrderResource;
use App\Models\Order;
use App\Models\ReturnOrder;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Parallax\FilamentComments\Actions\CommentsAction;
use Parallax\FilamentComments\Infolists\Components\CommentsEntry;
use Parallax\FilamentComments\Models\FilamentComment;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Overview')
                    ->schema([
                        TextEntry::make('shipping_address'),
                        TextEntry::make('postal.postal'),
                        TextEntry::make('city.name'),
                        TextEntry::make('country.name'),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('note')
                                ->default('No note stated')
                        ])
                    ])->columns(4),
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
                \Filament\Infolists\Components\Section::make('Status Details')
                    ->schema([
                        TextEntry::make('stage.name')
                            ->badge()
                            ->colors([

                            ]),
                        TextEntry::make('pipeline.name')
                            ->label('Order type'),
                    ])
                    ->icon('heroicon-m-clipboard-document-list')
                    ->columns(2),
                \Filament\Infolists\Components\Section::make('Shipping & Installation')
                    ->schema([
                        TextEntry::make('tracking_code')
                            ->default('Not available yet')
                            ->copyable(),
                        IconEntry::make('installation_required')
                            ->boolean()
                            ->label('Installation included'),
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
            Actions\Action::make('Create Return')
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
            Actions\EditAction::make(),
        ];
    }
}
