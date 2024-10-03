<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Resources\Json\JsonResource;

class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'is_special' => $this->is_special,
            'shift_hours' => $this->departmentShiftHoursMapping,
            'price_type' => $this->departmentPriceTypesMapping,
            'material_unit' => $this->departmentMaterialUnitsMapping,
            'required_certificates' => $this->requiredDepartmentCertificates,
            'optional_certificates' => $this->optionalDepartmentCertificates,
            'department_types' => $this->departmentTypes,
            'department_image' => getSingleMedia($this, 'department_image', null),
            'is_transport_option' => $this->is_transport_option,
            'is_experience' => $this->is_experience,
            'is_expected_salary' => $this->is_expected_salary,
            'is_relocate' => $this->is_relocate,
            'is_used_travelling' => $this->is_used_travelling,
            'is_notice_joining' => $this->is_notice_joining,
            'is_business_name' => $this->is_business_name,
            'is_designation' => $this->is_designation,
            'is_site_visit' => $this->is_site_visit,
            'is_preferred' => $this->is_preferred,
            'is_qualification' => $this->is_qualification,
            'is_plot_area' => $this->is_plot_area,
            'is_time_slots' => $this->is_time_slots,
            'is_advance_payment' => $this->is_advance_payment,
            'is_tax' => $this->is_tax,
            'is_interest_rates' => $this->is_interest_rates,
            'is_loan_process' => $this->is_loan_process,
            'is_discount_enabled' => $this->is_discount_enabled,
            'is_price_enabled' => $this->is_price_enabled,
            'is_multiple_location' => $this->is_multiple_location,
        ];
    }
}
