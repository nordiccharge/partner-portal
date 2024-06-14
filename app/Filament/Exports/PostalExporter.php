<?php

namespace App\Filament\Exports;

use App\Models\Order;
use App\Models\Postal;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class PostalExporter extends Exporter
{
    protected static ?string $model = Postal::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('installer.company.name')
                ->label('Installer'),
            ExportColumn::make('postal')
                ->label('Postal'),
            ExportColumn::make('city.name')
                ->label('City'),
            ExportColumn::make('country.name')
                ->label('Country'),
        ];
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Csv,
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your order export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
