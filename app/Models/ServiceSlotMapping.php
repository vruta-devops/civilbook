<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceSlotMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'days',
        'start_at',
        'end_at',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
