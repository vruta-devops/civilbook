<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ReportUser extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['user_id', 'reason', 'reported_by'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }
}
