<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
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
            'id'            => $this->id,
            'title'         => $this->title,
            'type'          => $this->type,
            'type_id'       => $this->type_id,
            'status'        => $this->status,
            'description'   => $this->description,
            'provider_name' => empty($this->provider) ? null : optional($this->provider)->first_name . " ". optional($this->provider)->last_name,
            'provider_address' => empty($this->providerAddress) ? null : optional($this->providerAddress)->address,
            'service_name'  => optional($this->service)->name,
            'slider_image'  => getSingleMedia($this, 'slider_image',null),
        ];
    }
}
