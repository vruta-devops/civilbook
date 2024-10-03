<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostJobAudioMapping extends Model
{
    use HasFactory;
    protected $table = 'post_job_audio_mappings';
    protected $fillable = [
        'post_request_id', 'post_job_attachment_audio'
    ];
    protected $casts = [
        'post_request_id' => 'integer'
    ];
}
