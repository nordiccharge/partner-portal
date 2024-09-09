<?php

namespace App\Filament\Exports;

use App\Models\Inventory;
use App\Models\Order;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OrderExporter extends Exporter
{
    protected static ?string $model = Order::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('team.name')
                ->label('Team'),
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('order_reference')
                ->label('Order Reference'),
            ExportColumn::make('pipeline.name')
                ->label('Pipeline'),
            ExportColumn::make('stage.name')
                ->label('Stage'),
            ExportColumn::make('customer_first_name')
                ->label('First Name'),
            ExportColumn::make('customer_last_name')
                ->label('Last Name'),
            ExportColumn::make('customer_email')
                ->label('Email'),
            ExportColumn::make('customer_phone')
                ->label('Phone'),
            ExportColumn::make('shipping_address')
                ->label('Shipping Address'),
            ExportColumn::make('postal.postal')
                ->label('Postal'),
            ExportColumn::make('city.name')
                ->label('City'),
            ExportColumn::make('country.name')
                ->label('Country'),
            ExportColumn::make('tracking_code')
                ->label('Tracking Code'),
            ExportColumn::make('installation_required')
                ->label('Installation Required'),
            ExportColumn::make('installation_date')
                ->label('Installation Date'),
            ExportColumn::make('created_at')
                ->label('Created At'),
        ];
    }

    public function getFormats(): array
    {
        return [
            ExportFormat::Csv,
            ExportFormat::Xlsx
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
