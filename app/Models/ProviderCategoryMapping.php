<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderCategoryMapping extends Model
{
    use HasFactory;
    protected $table = 'provider_category_mappings';
    protected $fillable = [
        'provider_id', 'category_id', 'sub_category_id', 'is_category_all', 'is_sub_category_all'
    ];
    protected $casts = [
        'provider_id' => 'integer',
        'category_id' => 'integer',
        'sub_category_id' => 'integer',
    ];

    public function category(){
        return $this->belongsTo(Category::class, 'category_id','id');
    }

    public function subCategory(){
        return $this->belongsTo(SubCategory::class, 'sub_category_id','id');
    }
}
