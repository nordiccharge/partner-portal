<?php

namespace App\Filament\Operation\Widgets;

use App\Models\Order;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class StatsOverview extends BaseWidget
{

    protected function queryOrdersBetween(Carbon $start, Carbon $end) {
        return Order::query()
            ->whereBetween('orders.created_at', [$start, $end])
            ->join('stages', 'orders.stage_id', '=', 'stages.id')
            ->where('stages.state', '!=', 'completed')
            ->where('stages.state', '!=', 'aborted')
            ->where('stages.state', '!=', 'return');
    }

    protected function ordersThisWeek() {
        return $this->queryOrdersBetween(now()->startOfWeek(), now()->endOfWeek())->count();
    }
    protected function ordersLastWeek() {
        return $this->queryOrdersBetween(now()->startOfWeek()->subWeek(), now()->endOfWeek()->subWeek())->count();
    }

    protected function weekDifference() {
        return $this->ordersThisWeek() - $this->ordersLastWeek();
    }
    protected function weekDifferenceDescription() {
        $difference = $this->weekDifference();
        if ($difference === 0)
            return 'No change since last week';
        return $difference > 0 ? 'Up by ' . $difference . ' orders since last week' : 'Down by ' . abs($difference) . ' orders since last week';
    }

    protected function ordersThisMonth() {
        return $this->queryOrdersBetween(now()->startOfMonth(), now()->endOfMonth())->count();
    }

    protected function ordersLastMonth() {
        return $this->queryOrdersBetween(now()->startOfMonth()->subMonth(), now()->endOfMonth()->subMonth())->count();
    }

    protected function monthDifference() {
        return $this->ordersThisMonth() - $this->ordersLastMonth();
    }

    protected function monthDifferenceDescription() {
        $difference = $this->monthDifference();
        if ($difference === 0)
            return 'No change since last month';
        return $difference > 0 ? 'Up by ' . $difference . ' orders since last month' : 'Down by ' . abs($difference) . ' orders since last month';
    }


    protected function getStats(): array
    {
        return [
            Stat::make('Orders this week', $this->ordersThisWeek())
                ->description($this->weekDifferenceDescription())
                ->descriptionIcon($this->weekDifference() ? ($this->weekDifference() > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down') : '')
                ->descriptionColor($this->weekDifference() ? ($this->weekDifference() > 0 ? 'success' : 'danger') : 'gray'),
            Stat::make('Orders this month', $this->ordersThisMonth())
                ->description($this->monthDifferenceDescription())
                ->descriptionIcon($this->monthDifference() ? ($this->monthDifference() > 0 ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down') : '')
                ->descriptionColor($this->monthDifference() ? ($this->monthDifference() > 0 ? 'success' : 'danger') : 'gray'),
        ];
    }
}
