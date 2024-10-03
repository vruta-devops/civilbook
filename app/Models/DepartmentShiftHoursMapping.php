<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentShiftHoursMapping extends Model
{
    use HasFactory;
    protected $table = 'department_shift_hours';
    protected $fillable = [
        'department_id', 'shift_hours_id'
    ];
    protected $casts = [
        'department_id'    => 'integer',
        'shift_hours_id' => 'integer',
    ];
    public function shifthours(){
        return $this->belongsTo(ShiftHour::class, 'shift_hours_id', 'id');
    }
}
