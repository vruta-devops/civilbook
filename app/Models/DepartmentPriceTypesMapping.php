<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentPriceTypesMapping extends Model
{
    use HasFactory;
    protected $table = 'department_prices';
    protected $fillable = [
        'department_id', 'price_types_id'
    ];
    protected $casts = [
        'department_id'    => 'integer',
        'price_types_id' => 'integer',
    ];
    public function priceTypes(){
        return $this->belongsTo(PriceTypes::class, 'price_types_id','id');
    }
}
