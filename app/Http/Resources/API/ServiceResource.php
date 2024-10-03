<?php

namespace App\Http\Resources\API;

use App\Models\ServicePackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $servicePackageCount = ServicePackage::whereIn('id', $this->servicePackage->pluck('service_package_id'))->where('status', 1)->get()->count();
        $user_id = request()->customer_id;
        if(isset($request->provider_id)) {
            $providers = $request->provider_id;
            $providers = explode(',', $providers);
        } else {
            $providers = $this->serviceProviderMapping->pluck('provider_id');
        }
        $image = getSingleMedia($this,'service_attachment', null);
        $file_extention = config('constant.IMAGE_EXTENTIONS');
        $extention = in_array(strtolower(imageExtention($image)),$file_extention);
        $currentDate = Carbon::now();

        $isDocumentUploaded = true;

        if (!empty($this->department) && !empty($this->department->requiredDepartmentCertificates)) {
            if ($this->certificates->count() == 0) {
                $isDocumentUploaded = false;
            }

            if ($this->uploadedRequiredCertificates->count() < $this->department->requiredDepartmentCertificates->count()) {
                $isDocumentUploaded = false;
            }
        }

        return [
            'distance' => $this->distance,
            'id'            => $this->id,
            'is_document_uploaded' => $isDocumentUploaded,
            'name'          => $this->name,
            'category_id'   => $this->category_id,
            'subcategory_id'=> $this->subcategory_id,
            'provider_id'   => $this->provider_id,
            'department_types' => $this->departmentType,
            'material_unit' => $this->materialUnit,
            'price'         => $this->price,
            'price_format'  => getPriceFormat($this->price),
            'type'          => $this->type,
            'discount'      => $this->discount,
            'duration'      => $this->duration,
            'status'        => $this->end_date >= $currentDate->toDateString() ? $this->status == 1 ? 1 : 0 : 0,
            'description'   => $this->description,
            'is_featured'   => $this->is_featured,
            'provider_name' => optional($this->providers)->display_name,
            'provider_image' => empty($this->providers) ? null : getSingleMedia(optional($this->providers), 'profile_image', null),
            'city_id' => optional($this->providers)->city_id,
            'department_name' => (!empty($this->category) && !empty($this->category->department)) ? $this->category->department->name : "-",
            'category_name'  => optional($this->category)->name,
            'subcategory_name'  => optional($this->subcategory)->name,
            'attchments' => getAttachments($this->getMedia('service_attachment')),
            'attchments_array' => getAttachmentArray($this->getMedia('service_attachment'),null),
            'total_rating'  => count($this->serviceRating) > 0 ? (float) number_format(max($this->serviceRating->avg('rating'),0), 2) : 0,
            'is_favourite'  => $this->getUserFavouriteService->where('user_id',$user_id)->first() ? 1 : 0,
            'service_address_mapping' => $this->providerServiceAddress,
            'attchment_extension' => $extention, //true:for png false: other
            'deleted_at' => $this->deleted_at,
            'is_slot'           => $this->is_slot,
            'slots'              => getServiceTimeSlot($this->provider_id ),
            'total_review' => count($this->serviceRating),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'user_service_status' => $this->user_service_status,
            //'digital_service'           => $this->digital_service,
            'providers' => UserResource::collection(User::where('status',1)->whereIn('id',$providers)->get()),
            'service_approval_status' => $this->user_service_status,
            'visit_type' => $this->visit_type,
            'is_enable_advance_payment' => $this->is_enable_advance_payment,
            'advance_payment_amount' => $this->advance_payment_amount == null ? 0 : (double)$this->advance_payment_amount,
            'admin_service_type' => $this->admin_service_type,
            'business_name' => $this->business_name,
            'designation' => $this->designation,
            'preferred_distance' => $this->preferred_distance,
            'tax' => $this->tax,
            'site_visit' => $this->site_visit,
            'charged_price' => $this->charged_price,
            'experience' => $this->experience,
            'expected_salary' => $this->expected_salary,
            'willing_to_relocate' => $this->willing_to_relocate,
            'user_for_travel' => $this->user_for_travel,
            'notice_period' => $this->notice_period,
            'qualification' => $this->qualification,
            'plot_area' => $this->plot_area,
            'interest_rate' => $this->interest_rate,
            'loan_process' => $this->loan_process,
            'with_transport' => $this->with_transport,
            'price_type' => $this->priceType,
            'added_by' => $this->addedBy,
            'service_package_added' => $servicePackageCount > 0
        ];
    }
}
