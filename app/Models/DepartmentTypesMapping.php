<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentTypesMapping extends Model
{
    use HasFactory;
    protected $table = 'department_types';
    protected $fillable = [
        'department_id', 'type_id'
    ];
    protected $casts = [
        'department_id'    => 'integer',
        'type_id' => 'integer',
    ];
    public function types(){
        return $this->belongsTo(Type::class, 'type_id','id');
    }
}
