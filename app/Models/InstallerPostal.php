<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallerPostal extends Model
{
    use HasFactory;

    protected $fillable = [
        'installer_id',
        'postal_id',
    ];

    public function installer()
    {
        return $this->belongsTo(Installer::class);
    }

    public function postal()
    {
        return $this->belongsTo(Postal::class);
    }
}
