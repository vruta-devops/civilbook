<?php

namespace App\Http\Resources\API;

use App\Http\Resources\ServiceCertificateResource;
use App\Models\Category;
use App\Models\ServicePackage;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user_id = request()->customer_id;
        $image = getSingleMedia($this,'service_attachment', null);
        $file_extention = config('constant.IMAGE_EXTENTIONS');
        $extention = in_array(strtolower(imageExtention($image)),$file_extention);
        $category = Category::with('department', 'department.requiredDepartmentCertificates', 'department.optionalDepartmentCertificates')->find($this->category_id);

        $department = null;
        if (!empty($category)) {
            $department = $category->department;
        }
        return [
            'id'            => $this->id,
            'certificates' => ServiceCertificateResource::collection($this->certificates),
            'service_slots' => $this->slots,
            'department' => $department,
            'department_types' => $this->departmentType,
            'price_type' => $this->priceType,
            'area' => empty($this->area) ? "" : $this->area,
            'shift_type' => $this->shiftType,
            'shift_hour' => $this->shiftHour,
            'qualification' => $this->qualification,
            'material_unit' => $this->materialUnit,
            'name'          => $this->name,
            'category_id'   => $this->category_id,
            'subcategory_id'   => $this->subcategory_id,
            'provider_id'   => $this->provider_id,
            'price'         => $this->price,
            'price_format'  => getPriceFormat($this->price),
            'type'          => $this->type,
            'discount'      => $this->discount,
            'duration'      => $this->duration,
            'status'        => $this->status,
            'description'   => $this->description,
            'is_featured'   => $this->is_featured,
            'provider_name' => optional($this->providers)->name,
            'category_name'  => optional($this->category)->name,
            'subcategory_name'  => optional($this->subcategory)->name,
            'attchments' => getAttachments($this->getMedia('service_attachment'),null),
            'attchments_array' => getAttachmentArray($this->getMedia('service_attachment'),null),
            'total_review'  => $this->serviceRating->count('id'),
            'total_rating'  => count($this->serviceRating) > 0 ? (float) number_format(max($this->serviceRating->avg('rating'),0), 2) : 0,
            'is_favourite'  => $this->getUserFavouriteService->where('user_id',$user_id)->first() ? 1 : 0,
            'service_address_mapping' => $this->providerServiceAddress,
            'attchment_extension' => $extention,
            'deleted_at' => $this->deleted_at,
            'is_slot'           => $this->is_slot,
            'slots'              => getServiceTimeSlot($this->provider_id ),
            'servicePackage'    => ServicePackageResource::collection(ServicePackage::whereIn('id',$this->servicePackage->pluck('service_package_id'))->where('status',1)->get()),
            'visit_type'           => $this->visit_type,
            'is_enable_advance_payment' => $this->is_enable_advance_payment,
            'advance_payment_amount' => $this->advance_payment_amount== null ? 0:(double) $this->advance_payment_amount,
            'admin_service_type' => $this->admin_service_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'business_name' => $this->business_name,
            'designation' => $this->designation,
            'preferred_distance' => $this->preferred_distance,
            'tax' => empty($this->tax) ? 0 : doubleval($this->tax),
            'site_visit' => $this->site_visit,
            'charged_price' => $this->charged_price,
            'experience' => $this->experience,
            'expected_salary' => $this->expected_salary,
            'willing_to_relocate' => $this->willing_to_relocate,
            'user_for_travel' => $this->user_for_travel,
            'notice_period' => $this->notice_period,
            'plot_area' => $this->plot_area,
            'interest_rate' => $this->interest_rate,
            'loan_process' => $this->loan_process,
            'with_transport' => $this->with_transport,
        ];
    }
}
