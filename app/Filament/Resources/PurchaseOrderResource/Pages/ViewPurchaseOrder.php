<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Overview')
                    ->schema([
                        TextEntry::make('note')
                    ]),
                \Filament\Infolists\Components\Section::make('Order Details')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Order ID')
                            ->copyable(),
                        TextEntry::make('updated_at'),
                        TextEntry::make('created_at')
                    ])
                    ->icon('heroicon-m-information-circle')
                    ->columns(4),
                \Filament\Infolists\Components\Section::make('Status Details')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->colors([

                            ]),
                    ])
                    ->icon('heroicon-m-clipboard-document-list')
                    ->columns(2),
                \Filament\Infolists\Components\Section::make('Shipping')
                    ->schema([

                        TextEntry::make('tracking_code')
                            ->default('Not available yet')
                            ->copyable()
                    ])->columns(4)
                    ->icon('heroicon-m-truck'),
                \Filament\Infolists\Components\Section::make('Cart Details')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->schema([
                                TextEntry::make('product.name')
                                    ->label('Name'),
                                TextEntry::make('quantity'),
                                TextEntry::make('product.sku')
                                    ->label('SKU'),
                                ImageEntry::make('product.image_url')
                                    ->label('')
                            ])->columns(2)
                    ])
                    ->icon('heroicon-m-shopping-bag')
            ]);
    }
}
