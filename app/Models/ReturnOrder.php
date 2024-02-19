<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'team_id',
        'reason',
        'state',
        'shipping_label',
        'pipeline_id'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function pipeline()
    {
        return $this->belongsTo(Pipeline::class);
    }

}
