<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'created_at', 'updated_at'];

    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_types');
    }
}
