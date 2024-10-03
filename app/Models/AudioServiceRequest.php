<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;

class AudioServiceRequest extends Model implements HasMedia
{
    use InteractsWithMedia,HasFactory,SoftDeletes;
    protected $table = 'audio_service_requests';
    protected $fillable = [
        'post_request_id', 'user_id', 'audio', 'description','status'
    ];

    protected $casts = [
        'user_id'   => 'integer',
        'status'    => 'integer',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User','user_id','id')->withTrashed();
    }
}
