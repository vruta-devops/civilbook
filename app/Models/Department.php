<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Department extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = ['name', 'status', 'is_transport_option', 'is_experience', 'is_expected_salary', 'is_relocate', 'is_used_travelling', 'is_notice_joining', 'is_business_name', 'is_designation', 'is_preferred', 'is_qualification', 'is_plot_area', 'is_advance_payment', 'is_tax', 'is_interest_rates', 'is_loan_process', 'is_site_visit', 'is_time_slots', 'is_discount_enabled', 'is_price_enabled', 'is_multiple_location'];

    public function catetories()
    {
        $this->hasMany(Category::class);
    }

    public function providers()
    {
        return $this->hasMany(User::class, 'department_id', 'id');
    }

    public function departmentShiftHoursMapping()
    {
        return $this->hasMany(DepartmentShiftHoursMapping::class, 'department_id', 'id');
    }

    public function departmentPriceTypesMapping()
    {
        return $this->hasMany(DepartmentPriceTypesMapping::class, 'department_id', 'id');
    }

    public function departmentMaterialUnitsMapping(){
        return $this->hasMany(DepartmentMaterialUnitsMapping::class, 'department_id','id');
    }

    public function departmentTypes()
    {
        return $this->belongsToMany(Type::class, 'department_types');
    }

    public function requiredDepartmentCertificates()
    {
        return $this->belongsToMany(Certificate::class, 'required_certificate_departments');
    }

    public function optionalDepartmentCertificates()
    {
        return $this->belongsToMany(Certificate::class, 'optional_certificate_departments');
    }
}
