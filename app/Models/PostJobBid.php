<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PostJobBid extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $fillable = [
        'post_request_id', 'provider_id', 'price' ,'duration','customer_id','bid_description'
    ];

    protected $casts = [
        'post_request_id'  => 'integer',
        'provider_id'  => 'integer',
        'customer_id'  => 'integer',
        'price' => 'double',
    ];

    public function provider(){
        return $this->belongsTo(User::class,'provider_id', 'id')->withTrashed();
    }
    public function customer(){
        return $this->belongsTo(User::class, 'customer_id', 'id')->withTrashed();
    }

    public function comments()
    {
        return $this->hasMany(BidComment::class);
    }

    public function scopeMyPostJobBid($query)
    {
        if(auth()->user()->hasRole('admin')) {
            return $query;
        }

        if(auth()->user()->hasRole('user')) {
            return $query->where('customer_id', \Auth::id());
        }
        if(auth()->user()->hasRole('provider')) {
            return $query->where('provider_id', \Auth::id());
        }
        return $query;
    }
    public function postrequest(){
        return $this->belongsTo(PostJobRequest::class,'post_request_id', 'id');
    }
}
