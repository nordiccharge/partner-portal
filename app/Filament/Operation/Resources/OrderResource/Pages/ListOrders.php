<?php

namespace App\Filament\Operation\Resources\OrderResource\Pages;

use App\Filament\Operation\Resources\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function tabQuery($tab) {
        if ($tab === 'missing_installer') {
            return Order::query()
                ->where('installation_required', '=', 1)
                ->whereNull('installer_id')
                ->join('stages', 'orders.stage_id', '=', 'stages.id')
                ->where('stages.state', '!=', 'completed')
                ->where('stages.state', '!=', 'aborted')
                ->where('stages.state', '!=', 'return')
                ->select('orders.*');
        }

        if ($tab === 'missing_installation_date') {
            return Order::query()
                ->where('installation_required', '=', 1)
                ->whereNull('installation_date')
                ->join('stages', 'stages.id', '=', 'orders.stage_id')
                ->where("orders.created_at", "<", now()->subDays(1))
                ->where('stages.state', '!=', 'completed')
                ->where('stages.state', '!=', 'aborted')
                ->where('stages.state', '!=', 'return')
                ->select('orders.*');
        }

        if ($tab === 'missing_tracking_code') {
            return Order::query()->whereNull('tracking_code')
                ->join('pipelines', 'pipelines.id', '=', 'orders.pipeline_id')
                ->where('pipelines.shipping', '=', '1')
                ->join('stages', 'stages.id', '=', 'orders.stage_id')
                ->where('stages.state', '!=', 'completed')
                ->where('stages.state', '!=', 'aborted')
                ->where('stages.state', '!=', 'return')
                ->where("orders.created_at", "<", now()->subDays(1))
                ->select('orders.*');
        }

        return Order::query()
            ->join('stages', 'stages.id', '=', 'orders.stage_id')
            ->where('stages.state', '!=', 'completed')
            ->where('stages.state', '!=', 'aborted')
            ->where('stages.state', '!=', 'return')
            ->select('orders.*');
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All orders')
                ->icon('heroicon-o-circle-stack')
                ->badge(function () {
                    return $this->tabQuery('')->count();
                })
                ->badgeColor(function () {
                    $count = $this->tabQuery('')->count();
                    if ($count>0) {
                        return 'info';
                    }
                    return 'gray';
                }),

            'missing_installer' => Tab::make('Missing installer')
                ->icon('heroicon-o-user')
                ->modifyQueryUsing(function (Builder $query) {
                    $query
                        ->where('installation_required', '=', 1)
                        ->whereNull('installer_id')
                        ->join('stages', 'orders.stage_id', '=', 'stages.id')
                        ->where('stages.state', '!=', 'completed')
                        ->where('stages.state', '!=', 'aborted')
                        ->where('stages.state', '!=', 'return')
                        ->select('orders.*');
                })
                ->badge(function () {
                    return $this->tabQuery('missing_installer')->count();
                })
                ->badgeColor(function () {
                    $count = $this->tabQuery('missing_installer')->count();
                    if ($count>0) {
                        return 'warning';
                    }
                    return 'gray';
                }),
            'missing_installation_date' => Tab::make('Missing installation date')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(function (Builder $query) {

                    $query
                        ->where('installation_required', '=', 1)
                        ->whereNull('installation_date')
                        ->join('stages', 'stages.id', '=', 'orders.stage_id')
                        ->where('stages.state', '!=', 'completed')
                        ->where('stages.state', '!=', 'aborted')
                        ->where('stages.state', '!=', 'return')
                        ->where("orders.created_at", "<", now()->subDays(1))
                        ->select('orders.*');
                })
                ->badge(function () {
                    return $this->tabQuery('missing_installation_date')->count();
                })
                ->badgeColor(function () {
                    $count = $this->tabQuery('missing_installation_date')->count();
                    if ($count>0) {
                        return 'warning';
                    }
                    return 'gray';
                }),
            'missing_tracking_code' => Tab::make('Missing tracking number')
                ->icon('heroicon-o-truck')
                ->modifyQueryUsing(function (Builder $query) {
                    $query->whereNull('tracking_code')
                        ->join('stages', 'stages.id', '=', 'orders.stage_id')
                        ->join('pipelines', 'pipelines.id', '=', 'orders.pipeline_id')
                        ->where('pipelines.shipping', '=', '1')
                        ->where('stages.state', '!=', 'completed')
                        ->where('stages.state', '!=', 'aborted')
                        ->where('stages.state', '!=', 'return')
                        ->where("orders.created_at", "<", now()->subDays(1))
                        ->select('orders.*');
                })
                ->badge(function () {
                    return $this->tabQuery('missing_tracking_code')->count();
                })
                ->badgeColor(function () {
                    $count = $this->tabQuery('missing_tracking_code')->count();
                    if ($count>0) {
                        return 'warning';
                    }
                    return 'gray';
                }),
            ];
    }
}
