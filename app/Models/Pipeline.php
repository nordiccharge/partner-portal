<?php

namespace App\Models;

use App\Enums\PipelineAutomation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pipeline extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'shipping_type',
        'automation_type',
        'shipping_price',
        'nc_price',
        'team_id',
        'shipping'
    ];

    protected $casts = [
        'automation_type' => PipelineAutomation::class
    ];

    public function stages(): HasMany {
        return $this->hasMany(Stage::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }

    public function team(): BelongsTo {
        return $this->belongsTo(Team::class);
    }
}
