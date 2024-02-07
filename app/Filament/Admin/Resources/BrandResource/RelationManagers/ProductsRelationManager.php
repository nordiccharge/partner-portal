<?php

namespace App\Filament\Admin\Resources\BrandResource\RelationManagers;

use App\Filament\Admin\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected function canCreate(): bool {
        return false;
    }

    protected function canEdit(Model $record): bool
    {
        return false;
    }

    protected function canDelete(Model $record): bool
    {
        return false;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image_url')
                    ->label(''),
                Tables\Columns\TextColumn::make('name')
                    ->description(fn (Product $record): string => $record->description ?: 'No description')
                    ->searchable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->badge()
                    ->color(function ($record) {
                        $quantity = $record->quantity;
                        if ($quantity > 0) {
                            return 'success';
                        }

                        if ($quantity < 0) {
                            return 'danger';
                        }

                        return 'primary';
                    }),
                Tables\Columns\TextColumn::make('category.name'),
                Tables\Columns\TextColumn::make('retail_price')
                    ->money('dkk'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('View Product')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => ProductResource::getUrl() . '/' . $record->id . '/edit'),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
