<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BadgeProvider extends Model
{
    protected $table = 'badge_provider';
    use HasFactory, SoftDeletes;
    /**
     * Get the badge that belongs to the BadgeProvider.
     */
    public function badge()
    {
        return $this->belongsTo(Badges::class);
    }

    /**
     * Get the provider that belongs to the BadgeProvider.
     */
    public function provider()
    {
        return $this->belongsTo(User::class);
    }
}
