<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;


class PostJobBiderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'post_request_id'       => $this->post_request_id,
            'provider_id'           => $this->provider_id,
            'price'                 => $this->price,
            'duration'              => $this->duration,
            'bid_description'       => $this->bid_description,
            'attachments' => getAttachments($this->getMedia('post_job_bids'), null),
            'provider'              => new UserResource($this->provider),
            'customer' => new UserResource($this->customer),
            'comments' => $this->comments,
            'post_detail'           => new PostJobRequestResource($this->postrequest)
        ];
    }
}
