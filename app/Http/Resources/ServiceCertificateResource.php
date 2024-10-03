<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCertificateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $serviceDocument = getSingleMedia($this, 'provider_document', null);
        return [
            "id" => $this->id,
            "provider_id" => $this->provider_id,
            "service_id" => $this->service_id,
            "certificate_id" => $this->certificate_id,
            "is_approved" => $this->is_approved,
            "is_required" => $this->is_required,
            "document" => $serviceDocument,
            "certificate" => $this->certificate
        ];
    }
}
