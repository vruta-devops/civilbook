<?php

namespace App\Http\Resources\API;

use App\Models\Category;
use App\Models\PostJobBid;
use App\Models\Service;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PostJobRequestDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {

        $user = auth()->user();
        $can_bid = null;
        if ($user->hasRole('provider')) {
            $can_bid = true;
            $count = PostJobBid::where('provider_id', $user->id)->where('post_request_id', $this->id)->get();
            if (count($count) > 0) {
                $can_bid = false;
            }
        }

        $catNameResource = CategoryResource::collection(Category::whereIn('id', $this->postCategoryMapping->pluck('category_id'))->select('name')->get());
        $SubCatNameResource = SubCategoryResource::collection(SubCategory::whereIn('id', $this->postCategoryMapping->pluck('sub_category_id'))->select('name')->get());

        $subCatName = 'All';

        if ($this->is_all_sub_categories == false) {
            if (!empty($SubCatNameResource)) {
                $subCatName = $SubCatNameResource[0]->name;
            }
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'attachments' => getAttachments($this->getMedia('post_job_attachment'), null),
            'reason' => $this->reason,
            'price' => $this->price,
            'provider_id' => $this->provider_id,
            'customer_id' => $this->customer_id,
            'customer_name' => optional($this->customer)->display_name,
            'customer_profile' => getSingleMedia($this->customer, 'profile_image', null),
            'status' => $this->status,
            'can_bid' => $can_bid,
            'service' => ServiceResource::collection(Service::whereIn('id', $this->postServiceMapping->pluck('service_id'))->get()),
            'job_price' => $this->job_price,
            'category_name' => (!empty($catNameResource) && isset($catNameResource[0])) ? $catNameResource[0]->name : "",
            'sub_category_name' => $subCatName,
            // 'CategoryName'     => CategoryResource::collection(Category::whereIn('id',$this->postCategoryMapping->pluck('category_id'))->select('name')->pluck('name')),
        ];
    }
}
