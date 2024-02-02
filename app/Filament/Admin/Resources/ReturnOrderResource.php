<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Admin\Resources\ReturnOrderResource\Pages;
use App\Filament\Admin\Resources\ReturnOrderResource\RelationManagers;
use App\Models\Order;
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
    protected static ?string $navigationGroup = 'Global Operations';
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
            return 'warning';
        }
        elseif (static::getActionOrders()->count() === 0) {
            return 'primary';
        }
        else {
            return 'info';
        }
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('team_id')
                    ->relationship('team', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('order_id')
                    ->relationship('order', 'id', fn (Builder $query, Forms\Get $get) => $query->where('team_id', '=', $get('team_id')))
                    ->searchable()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->preload(),
                Forms\Components\Textarea::make('reason')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('state')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                    ])
                    ->required(),
                Forms\Components\Select::make('shipping_label')
                    ->options([
                        'true' => 'Create Shipping Label Automatically',
                        'false' => 'No Label',
                    ])
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => OrderResource::getUrl() . '/' . $record->order->id),
                Tables\Columns\TextColumn::make('team.name')
                    ->badge(),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Complete Return')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->hidden(fn (ReturnOrder $record) => $record->state != 'processing')
                    ->action(fn (ReturnOrder $record) => $record->update(['state' => 'completed'])),
                Tables\Actions\Action::make('Start Processing')
                    ->icon('heroicon-o-arrow-right')
                    ->color('info')
                    ->requiresConfirmation()
                    ->hidden(fn (ReturnOrder $record) => $record->state != 'pending')
                    ->action(fn (ReturnOrder $record) => $record->update(['state' => 'processing'])),
                Tables\Actions\EditAction::make()
                    ->hidden(fn (ReturnOrder $record) => $record->state === 'completed'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'create' => Pages\CreateReturnOrder::route('/create'),
            'edit' => Pages\EditReturnOrder::route('/{record}/edit'),
        ];
    }
}
