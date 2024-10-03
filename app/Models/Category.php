<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class Category extends BaseModel implements HasMedia
{
    use HasFactory,HasRoles,InteractsWithMedia,SoftDeletes;
    protected $table = 'categories';
    protected $fillable = [
        'name', 'description', 'is_featured', 'status', 'color', 'department_id'
    ];
    protected $casts = [
        'status'    => 'integer',
        'is_featured'  => 'integer',
    ];

    public function services(){
        return $this->hasMany(Service::class, 'category_id','id');
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'category_id', 'id');
    }
    public function scopeList($query)
    {
        return $query->orderBy('deleted_at', 'asc');
    }
    public function scopeMyCategory($query)
    {
        if(auth()->user()->hasRole('provider')) {
            return $query->join('provider_category_mappings','provider_category_mappings.category_id','=','categories.id')->where('categories.status',1)->where('provider_category_mappings.provider_id',auth()->user()->id)->select('categories.*')->groupBy('categories.id');
    }
        return $query;
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
