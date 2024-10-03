<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftType extends Model
{
    use HasFactory;

    public function shiftHours()
    {
        return $this->hasMany(ShiftHour::class, 'shift_type_id', 'id');
    }
}
