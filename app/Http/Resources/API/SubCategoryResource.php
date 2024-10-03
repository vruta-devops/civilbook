<?php

namespace App\Http\Resources\API;

use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SubCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $categoryId = $request->category_id ?? $this->category_id;

        // Calculate the service count
        $servicesCount = Service::where(function ($query) {
            $query->where('subcategory_id', $this->id);
        })
            ->where('end_date', '>=', Carbon::now()->toDateString())
            ->where('category_id', $categoryId)
            ->where('user_service_status', 1);

        $data = $request->all();

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $radius = !empty($data['radius']) ? $data['radius'] : 30;
            $servicesCount = $servicesCount->join('provider_service_address_mappings', 'services.id', '=', 'provider_service_address_mappings.service_id')
                ->join('provider_address_mappings', 'provider_service_address_mappings.provider_address_id', '=', 'provider_address_mappings.id')
                ->select('services.*', 'provider_address_mappings.id as address_id', 'provider_address_mappings.latitude', 'provider_address_mappings.longitude')
                ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(provider_address_mappings.latitude)) * cos(radians(provider_address_mappings.longitude) - radians(?)) + sin(radians(?)) * sin(radians(provider_address_mappings.latitude)))) AS distance', [$data['latitude'], $data['longitude'], $data['latitude']])->having('distance', '<=', $radius)->groupBy('services.id');
        }
        $servicesCount = $servicesCount->count();

        $extension = imageExtention(getSingleMedia($this, 'subcategory_image', null));
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'status'           => $this->status,
            'department_types' => !empty($this->departmentTypes) ? $this->departmentTypes : [],
            'description'      => $this->description,
            'is_featured'      => $this->is_featured,
            'color'            => $this->color,
            'category_id'      => $this->category_id,
            'category_image'=> getSingleMedia($this, 'subcategory_image',null),
            'category_extension' => $extension,
            'category_name' => optional($this->category)->name,
            'services' => $servicesCount,
            'deleted_at' => $this->deleted_at
        ];
    }
}
