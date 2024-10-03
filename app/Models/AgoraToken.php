<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgoraToken extends Model
{
    use HasFactory;

    protected $fillable = ['sender_id', 'receiver_id', 'common_token', 'audio_token', 'channel_name'];
}
