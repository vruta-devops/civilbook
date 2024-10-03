<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;


class PostJobRequest extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    protected $table = 'post_job_requests';
    protected $fillable = [
        'title', 'customer_id', 'status', 'description', 'provider_id', 'reason', 'price', 'date', 'job_price', 'is_all_sub_categories',
        'expired_at', 'address', 'latitude', 'longitude', 'post_job_attachment'
    ];

    protected $casts = [
        'customer_id'  => 'integer',
        'provider_id'  => 'integer',
        'price' => 'double',
        'job_price' => 'double',
    ];
    public function postServiceMapping(){
        return $this->hasMany(PostJobServiceMapping::class, 'post_request_id','id');
    }
    public function postCategoryMapping(){
        return $this->hasMany(PostJobCategoryMapping::class, 'post_request_id','id');
    }
    public function postAudioMapping(){
        return $this->hasMany(PostJobAudioMapping::class, 'post_request_id','id');
    }
    public function scopeMyPostJob($query)
    {
        $query->orderBy('id', 'desc');

        if(auth()->user()->hasRole('admin')) {
            return $query;
        }

        if(auth()->user()->hasRole('user')) {
            return $query->where('customer_id', \Auth::id());
        }
        if(auth()->user()->hasRole('provider')) {
              //get provider category mapping ids => get job post add condition category in provider mapping ids
              //return $query->join('post_job_category_mappings','post_job_category_mappings.post_request_id','=','post_job_requests.id')->join('provider_category_mappings','provider_category_mappings.category_id','=','post_job_category_mappings.category_id')->where('provider_category_mappings.provider_id','=',auth()->user()->id)->select('post_job_requests.*')->groupBy('post_job_requests.id');
              return $query->join('post_job_category_mappings as pjcm','pjcm.post_request_id','=','post_job_requests.id')
              ->join('provider_category_mappings as pcm1','pcm1.category_id','=','pjcm.category_id')
              ->join('provider_category_mappings as pcm2','pcm2.sub_category_id','=','pjcm.sub_category_id')
              ->where('pcm1.provider_id','=',auth()->user()->id)
              ->where('pcm2.provider_id','=',auth()->user()->id)
                  ->select('post_job_requests.*')->where('post_job_requests.created_at', ">=", auth()->user()->created_at)->groupBy('post_job_requests.id');
        }
        return $query;
    }
    public function postBidList(){
        return $this->hasMany(PostJobBid::class, 'post_request_id','id');
    }
    public function provider(){
        return $this->belongsTo(User::class,'provider_id', 'id')->withTrashed();
    }
    public function customer(){
        return $this->belongsTo(User::class,'customer_id', 'id')->withTrashed();
    }
}
