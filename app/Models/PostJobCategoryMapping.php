<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobCategoryMapping extends Model
{
    use HasFactory;
    protected $table = 'post_job_category_mappings';
    protected $fillable = [
        'post_request_id', 'category_id', 'sub_category_id'
    ];
    protected $casts = [
        'post_request_id' => 'integer',
        'category_id' => 'integer',
    ];
}
