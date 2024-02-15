<?php

namespace App\Http\Middleware;

use App\Models\Inventory;
use App\Models\Order;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyPartnerPanelScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Inventory::addGlobalScope(
            function (Builder $query) {
                $query
                    ->where('team_id', '=', Filament::getTenant()->id)
                    ->orWhere('global', '=', 1);
            }
        );

        Order::addGlobalScope(
            fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant()),
        );

        return $next($request);
    }
}
