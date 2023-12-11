<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Charger extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'order_id',
        'serial_number',
        'service'
    ];

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

}
