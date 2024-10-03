<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Service extends Model implements  HasMedia
{
    use InteractsWithMedia,HasFactory,SoftDeletes;

    protected $table = 'services';
    protected $guarded = ['id'];
    protected $with = ['addedBy'];
    protected $fillable = [
        'name', 'category_id', 'provider_id' , 'type' , 'is_slot','discount' , 'duration' ,'description',
        'is_featured', 'status' , 'price' , 'added_by','subcategory_id','service_type','visit_type',
        'is_enable_advance_payment', 'advance_payment_amount', 'start_date', 'end_date', 'user_service_status', 'admin_service_type', 'department_id', 'address', 'latitude', 'longitude', 'post_job_attachment', 'type_id', 'price_type_id', 'shift_type_id', 'shift_hour_id', 'material_unit_id', 'business_name', 'designation', 'preferred_distance', 'tax', 'site_visit',
        'charged_price', 'experience', 'expected_salary', 'willing_to_relocate', 'user_for_travel', 'notice_period', 'plot_area', 'interest_rate', 'loan_process', 'with_transport', 'qualification', 'area', 'is_post_job',
    ];

    protected $casts = [
        'department_id' => 'integer',
        'category_id'               => 'integer',
        'subcategory_id'               => 'integer',
        'provider_id'               => 'integer',
        'price'                     => 'double',
        'discount'                  => 'double',
        'status'                    => 'integer',
        'is_featured'               => 'integer',
        'added_by'                  => 'integer',
        'is_slot'                   => 'integer',
        //'digital_service'           => 'integer',
        'is_enable_advance_payment' => 'integer',
        'advance_payment_amount'    => 'double',
//        'start_date'                => 'date',
//        'end_date'                  => 'date',
    ];

    public function providers(){
        return $this->belongsTo('App\Models\User','provider_id','id')->withTrashed();
    }

    public function commonServiceProviders(): BelongsToMany
    {
        return $this->belongsToMany(User::class, ServiceProviderMapping::class, 'service_id', 'provider_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by', 'id')->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo('App\Models\Department', 'department_id', 'id')->withTrashed();
    }


    public function category(){
        return $this->belongsTo('App\Models\Category','category_id','id')->withTrashed();
    }
    public function subcategory(){
        return $this->belongsTo('App\Models\SubCategory','subcategory_id','id')->withTrashed();
    }
    public function serviceRating(){
        return $this->hasMany(BookingRating::class, 'service_id','id')->orderBy('created_at','desc');
    }
    public function serviceBooking(){
        return $this->hasMany(Booking::class, 'service_id','id');
    }
    public function serviceCoupons(){
        return $this->hasMany(CouponServiceMapping::class, 'service_id','id');
    }

    public function getUserFavouriteService(){
        return $this->hasMany(UserFavouriteService::class, 'service_id','id');
    }

    public function providerAddress(){
        return $this->hasMany(ProviderAddressMapping::class, 'provider_id','id');
    }

    public function providerServiceAddress(){
        return $this->hasMany(ProviderServiceAddressMapping::class, 'service_id','id')->with('providerAddressMapping');
    }

    protected static function boot()
    {
        parent::boot();
        static::deleted(function ($row) {
            $row->serviceBooking()->delete();
            $row->serviceCoupons()->delete();
            $row->serviceRating()->delete();
            $row->getUserFavouriteService()->delete();

            if($row->forceDeleting === true)
            {
                $row->serviceRating()->forceDelete();
                $row->serviceCoupons()->forceDelete();
                $row->serviceBooking()->forceDelete();
                $row->getUserFavouriteService()->forceDelete();
            }
        });

        static::restoring(function($row) {
            $row->serviceRating()->withTrashed()->restore();
            $row->serviceCoupons()->withTrashed()->restore();
            $row->serviceBooking()->withTrashed()->restore();
            $row->getUserFavouriteService()->withTrashed()->restore();
        });
    }
    public function scopeMyService($query)
    {
        if(auth()->user()->hasRole('admin')) {

            return $query->where('service_type', 'service')->withTrashed();
        }

        if(auth()->user()->hasRole('provider')) {
            //return $query->where('provider_id', \Auth::id());
            return $query->join('service_provider_mappings','service_provider_mappings.service_id','=','services.id')->where('service_provider_mappings.provider_id','=',auth()->user()->id)->select('services.*')->groupBy('services.id');
        }

        return $query;
    }

    public function scopeLocationService($query, $latitude = '', $longitude = '', $radius = 50, $unit = 'km'){
        if(default_earning_type() === 'subscription'){
            $provider = User::where('user_type','provider')->where('status',1)->where('is_subscribe',1)->pluck('id');
        }else{
            $provider = User::where('user_type','provider')->where('status',1)->pluck('id');
        }
        $unit_value = countUnitvalue($unit);
        $near_location_id = ProviderAddressMapping::selectRaw("id, provider_id, address, latitude, longitude,
                ( $unit_value * acos( cos( radians($latitude) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians($longitude)
                ) + sin( radians($latitude) ) *
                sin( radians( latitude ) ) )
                ) AS distance")
        ->where('status',1)
        ->whereIn('provider_id',$provider)
        ->having("distance", "<=", $radius)
        ->orderBy("distance",'asc')
        ->get()->pluck('id');
        return $near_location_id;
    }
    public function scopeList($query)
    {
        return $query->orderBy('deleted_at', 'asc');
    }
    public function servicePackage(){
        return $this->hasMany(PackageServiceMapping::class, 'service_id','id');
    }
    public function postJobService(){
        return $this->hasMany(PostJobServiceMapping::class, 'service_id', 'id');
    }
    public function serviceProviderMapping(){
       return $this->hasMany(ServiceProviderMapping::class, 'service_id', 'id')->with('providers');
    }

    public function serviceAddon(){
        return $this->hasMany(ServiceAddon::class, 'service_id','id');
    }

    public function departmentType()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function priceType()
    {
        return $this->belongsTo(PriceTypes::class, 'price_type_id', 'id');
    }

    public function shiftType()
    {
        return $this->belongsTo(ShiftType::class, 'shift_type_id', 'id');
    }

    public function shiftHour()
    {
        return $this->belongsTo(ShiftHour::class, 'shift_hour_id', 'id');
    }

    public function materialUnit()
    {
        return $this->belongsTo(MaterialUnits::class, 'material_unit_id', 'id');
    }

    public function slots()
    {
        return $this->hasMany(ServiceSlotMapping::class);
    }

    public function certificates()
    {
        return $this->hasMany(ServiceCertificate::class);
    }

    public function uploadedRequiredCertificates()
    {
        return $this->hasMany(ServiceCertificate::class)->where('is_required', 1);
    }

    public function verifiedRequiredCertificates()
    {
        return $this->hasMany(ServiceCertificate::class)->where('is_required', 1)->where('is_approved', 1);
    }
}
