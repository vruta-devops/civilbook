<?php

namespace App\Http\Resources\API;

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class PostJobRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = auth()->user();
        $can_bid = null;
        if($user->hasRole('provider')){
          $can_bid = true;
          $count = count($this->postBidList->where('provider_id',$user->id));
          if($count > 0){
            $can_bid = false;
          }
        }

        $expireInDays = 0;
        if (!empty($this->expired_at)) {
            $expiredAt = Carbon::parse($this->expired_at);

            if (!$expiredAt->isPast()) {
                $expiredAt = $expiredAt->addDay();
            }
            $expireInDays = Carbon::now()->diffInDays($expiredAt, false);
        }

        return [
            'id'                => $this->id,
            'title'             => $this->title,
            'description'       => $this->description,
            'reason'            => $this->reason,
            'price'             => $this->price,
            'provider_id'       => $this->provider_id,
            'expired_in_days' => $expireInDays,
            'customer_id'       => $this->customer_id,
            'status'            => $this->status,
            'can_bid'           =>  $can_bid,
            'service'           => ServiceResource::collection(Service::whereIn('id',$this->postServiceMapping->pluck('service_id'))->get()),
            'created_at'            => $this->created_at,
            'job_price'             => $this->job_price,

        ];
    }
}
