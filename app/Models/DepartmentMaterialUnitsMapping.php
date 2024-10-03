<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentMaterialUnitsMapping extends Model
{
    use HasFactory;
    protected $table = 'department_material_units';
    protected $fillable = [
        'department_id', 'material_unit_id'
    ];
    protected $casts = [
        'department_id'    => 'integer',
        'material_unit_id' => 'integer',
    ];

    public function materialUnits()
    {
        return $this->belongsTo(MaterialUnits::class, 'material_unit_id', 'id');
    }
}
