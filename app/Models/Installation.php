<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Installation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kw',
        'price',
        'team_id'
    ];

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }

    public function order(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
