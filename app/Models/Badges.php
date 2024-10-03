<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badges extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'badge_color'];

     /**
     * The providers that belong to the badge.
     */
    public function providers()
    {
        return $this->belongsToMany(Provider::class, 'badge_provider');
    } 
}