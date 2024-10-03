<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingProviderMapping extends Model
{
    use HasFactory,SoftDeletes;

    protected $table = 'booking_provider_mappings';
    protected $fillable = [
        'booking_id', 'provider_id'
    ];
    
    protected $casts = [
        'booking_id'    => 'integer',
        'provider_id'   => 'integer',
    ];
    
    public function providers(){
        return $this->belongsTo(User::class,'provider_id', 'id');
    }
    public function bookings(){
        return $this->belongsTo(Booking::class,'booking_id', 'id');
    }
}
