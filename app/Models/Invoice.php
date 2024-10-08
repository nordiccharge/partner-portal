<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Invoice extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'note',
        'total_price',
        'vat',
        'invoiceable_id',
        'invoiceable_type',
        'status',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class
    ];

    protected static $logAttributes = ['*'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['updated_at'])
            ->logOnlyDirty();
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getTotalPriceAttribute()
    {
        $total_price = 0;
        foreach ($this->items as $item) {
            $total_price += $item->price * $item->quantity;
        }

        return $total_price;
    }
}
