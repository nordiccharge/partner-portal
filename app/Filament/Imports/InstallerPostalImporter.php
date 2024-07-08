<?php

namespace App\Filament\Imports;

use App\Models\Installer;
use App\Models\InstallerPostal;
use App\Models\Postal;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;

class InstallerPostalImporter extends Importer
{
    protected static ?string $model = InstallerPostal::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('postal')
                ->requiredMapping()
                ->relationship('postal', 'postal')
                ->numeric()
                ->rules(['required', 'integer']),
        ];
    }

    public function resolveRecord(): ?InstallerPostal
    {
        return InstallerPostal::firstOrNew([
            'postal_id' => Postal::where('postal', $this->data['postal'])->firstOrFail()->id,
            'installer_id' => $this->options['installer_id'],
        ]);

    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your installer postal import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
