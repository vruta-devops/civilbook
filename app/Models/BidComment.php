<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BidComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_job_bid_id', 'comment', 'sender_type'
    ];

    public function postJobBid()
    {
        $this->belongsTo(PostJobBid::class);
    }
}
