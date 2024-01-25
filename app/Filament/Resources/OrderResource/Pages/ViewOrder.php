<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Inventory;
use App\Models\Order;
use Faker\Provider\Text;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

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
                        TextEntry::make('city.name'),
                        TextEntry::make('country.name'),
                        \Filament\Infolists\Components\Section::make([
                            TextEntry::make('note')
                                ->default('No note stated')
                        ])
                    ])->columns(3),
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
                    ->icon('heroicon-m-shopping-bag')
            ]);
    }


}
