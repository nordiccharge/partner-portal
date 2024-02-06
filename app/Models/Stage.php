<?php

namespace App\Models;

use App\Enums\StageAutomation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Stage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'order',
        'pipeline_id',
        'state',
        'description',
        'automation_type'
    ];

    protected $casts = [
        'automation_type' => StageAutomation::class
    ];

    public function pipeline(): BelongsTo {
        return $this->belongsTo(Pipeline::class);
    }

    public function orders(): HasMany {
        return $this->hasMany(Order::class);
    }
}
