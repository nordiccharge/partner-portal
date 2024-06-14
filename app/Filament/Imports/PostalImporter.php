<?php

namespace App\Filament\Imports;

use App\Models\Installer;
use App\Models\Postal;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class PostalImporter extends Importer
{
    protected static ?string $model = Postal::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('Installer')
                ->label('Installer')
                ->relationship(resolveUsing: function (string $state): ?Installer {
                    return Installer::whereHas('company', function (\Illuminate\Database\Eloquent\Builder $query) use ($state) {
                        $query->where('name', '=', $state);
                    })->first();
                }),
            ImportColumn::make('Postal')
                ->label('Postal')
                ->requiredMapping()
                ->numeric()
                ->rules(['required', 'integer']),
            ImportColumn::make('City')
                ->label('City')
                ->requiredMapping()
                ->relationship('city', 'name')
                ->rules(['required']),
            ImportColumn::make('Country')
                ->requiredMapping()
                ->relationship('country', 'short_name')
                ->rules(['required']),
        ];
    }

    public function resolveRecord(): ?Postal
    {
        return Postal::firstOrNew([
            'postal' => $this->data['postal'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your postal import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
