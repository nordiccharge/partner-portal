<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TeamResource\Pages;
use App\Filament\Admin\Resources\TeamResource\RelationManagers;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamResource extends Resource
{
    protected static ?string $model = Team::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationGroup = 'Administration';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Team Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->unique(ignoreRecord: true)
                            ->required(),
                        Forms\Components\Select::make('company_id')
                            ->required()
                            ->relationship('company', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('user_id')
                            ->label('Owner')
                            ->required()
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Charger Backend Service API')
                    ->schema([
                        Forms\Components\Toggle::make('backend_api')
                            ->label('Enable Backend API')
                            ->helperText('Forces Chargers to be automatically integrated and connected with backend service using service provider.'),
                        Forms\Components\Select::make('backend_api_service')
                            ->label('Backend Service Provider')
                            ->options([
                                'monta' => 'Monta',
                                'eosvolt' => 'EOSVolt'
                            ])
                            ->disabled(
                                fn (Forms\Get $get): bool => $get('backend_api') == false
                            )
                    ])
                    ->live()
                    ->columns(2),
                Forms\Components\Section::make('Partner Portal API')
                    ->schema([
                        Forms\Components\Toggle::make('basic_api')
                            ->label('Enable Basic API')
                            ->helperText('Enables list products and add, get, list and update orders by API'),
                        Forms\Components\Toggle::make('woocommerce_api')
                            ->label('Enable WooCommerce API')
                                ->helperText('Enables WooCommerce order updates by team webhook')
                            ->default(false),
                        Forms\Components\Toggle::make('shipping_api_send')
                            ->label('Send orders to shipping system')
                            ->helperText('Only for "Private Installation" pipeline orders')
                            ->default(false),
                        Forms\Components\Toggle::make('shipping_api_get')
                            ->label('Get fulfillment from shipping system')
                            ->helperText('Enables shipping system order updates by team webhook')
                            ->default(false),
                        Forms\Components\TextInput::make('secret_key')
                            ->helperText('The secret key must only be shared with executives of the parent team company')
                            ->unique(ignoreRecord: true)
                            ->required()
                            ->disabled()
                            ->readOnly(true)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('Webhook Configuration')
                    ->schema([
                        Forms\Components\Toggle::make('endpoint')
                            ->label('Enable Webhook')
                            ->default(false),
                        Forms\Components\TextInput::make('endpoint_url')
                            ->label('Webhook Endpoint URL')
                            ->url()
                            ->disabled(
                                fn (Forms\Get $get): bool => $get('endpoint') == false
                            )
                    ])
                    ->live()
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('company.name')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            RelationManagers\UsersRelationManager::class,
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\InventoriesRelationManager::class,
            RelationManagers\PurchaseOrdersRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTeams::route('/'),
            'create' => Pages\CreateTeam::route('/create'),
            'edit' => Pages\EditTeam::route('/{record}/edit'),
        ];
    }
}
