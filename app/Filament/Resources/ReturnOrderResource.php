<?php

namespace App\Filament\Resources;

use App\Filament\Admin\Resources\OrderResource;
use App\Filament\Resources\ReturnOrderResource\Pages;
use App\Filament\Resources\ReturnOrderResource\RelationManagers;
use App\Models\ReturnOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class ReturnOrderResource extends Resource
{
    protected static ?string $model = ReturnOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    protected static ?string $navigationGroup = 'Management';
    protected static ?string $navigationLabel = 'Returns';
    protected static ?int $navigationSort = 1;

    private static function getActionOrders(): Collection
    {
        return static::getModel()::where('state', '!=', 'completed')->get();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getActionOrders()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        if (static::getActionOrders()->count() > 0) {
            return 'info';
        }
        elseif (static::getActionOrders()->count() === 0) {
            return 'primary';
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => OrderResource::getUrl() . '/' . $record->order->id)
                    ->label('Order ID'),
                Tables\Columns\TextColumn::make('pipeline.name'),
                Tables\Columns\TextColumn::make('state')
                    ->badge()
                    ->sortable()
                    ->color(
                        function (ReturnOrder $record) {
                            if ($record->state === 'processing') {
                                return 'info';
                            }
                            if ($record->state === 'completed') {
                                return 'gray';
                            }
                            if ($record->state === 'pending') {
                                return 'warning';
                            }
                        }
                    )
                    ->searchable(),
                Tables\Columns\TextColumn::make('reason'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([

            ])
            ->bulkActions([

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReturnOrders::route('/'),
        ];
    }
}
