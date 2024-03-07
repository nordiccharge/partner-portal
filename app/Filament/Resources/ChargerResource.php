<?php

namespace App\Filament\Resources;

use App\Events\TicketCreated;
use App\Filament\Resources\ChargerResource\Pages;
use App\Filament\Resources\ChargerResource\RelationManagers;
use App\Models\Charger;
use App\Models\Order;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChargerResource extends Resource
{
    protected static ?string $model = Charger::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Management';

    public static function getNavigationBadge(): ?string
    {
        return Filament::getTenant()->chargers()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Order')
                    ->preload()
                    ->searchable()
                    ->options(Order::all()->pluck('id', 'id')->toArray())
                    ->nullable(),
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->preload()
                    ->searchable()
                    ->options(\App\Models\Product::all()->pluck('name', 'id')->toArray())
                    ->required(),
                Forms\Components\TextInput::make('serial_number')
                    ->nullable()
                    ->maxLength(255),
                Forms\Components\TextInput::make('service')
                    ->nullable()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.customer_first_name')
                    ->label('Full name')
                    ->formatStateUsing(fn (Charger $record) => $record->order->customer_first_name . ' ' . $record->order->customer_last_name)
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('order.shipping_address')
                    ->label('Address')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('order.id')
                    ->label('Order ID'),
                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Support')
                    ->color('primary')
                    ->icon('heroicon-o-question-mark-circle')
                    ->modalIcon('heroicon-o-question-mark-circle')
                    ->modalHeading('Create Support Ticket')
                    ->modalDescription('Please provide a detailed description of your issue or question')
                    ->modalSubmitActionLabel('Create Ticket')
                    ->modalWidth('xl')
                    ->form([
                        Select::make('type')
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
                    ->action( function (Charger $record, array $data) {
                        // Send support ticket
                        TicketCreated::dispatch($record, $data, 'Charger');
                    }),
                Tables\Actions\EditAction::make()
                    ->visible(auth()->user()->isAdmin()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(auth()->user()->isAdmin()),
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
            'index' => Pages\ListChargers::route('/'),
            'create' => Pages\CreateCharger::route('/create'),
            'edit' => Pages\EditCharger::route('/{record}/edit'),
        ];
    }
}
