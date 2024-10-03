<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\AppDownloadResource;
use App\Http\Resources\API\BlogResource;
use App\Http\Resources\API\BookingRatingResource;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\CategoryResource;
use App\Http\Resources\API\CommonServiceResource;
use App\Http\Resources\API\DepartmentResource;
use App\Http\Resources\API\HandymanRatingResource;
use App\Http\Resources\API\PaymentGatewayResource;
use App\Http\Resources\API\PostJobRequestResource;
use App\Http\Resources\API\ServicePackageResource;
use App\Http\Resources\API\ServiceResource;
use App\Http\Resources\API\SliderResource;
use App\Http\Resources\API\UserResource;
use App\Models\AppDownload;
use App\Models\AppSetting;
use App\Models\Badges;
use App\Models\Blog;
use App\Models\Booking;
use App\Models\BookingHandymanMapping;
use App\Models\BookingRating;
use App\Models\Category;
use App\Models\Department;
use App\Models\HandymanPayout;
use App\Models\HandymanRating;
use App\Models\HandymanType;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\PostJobRequest;
use App\Models\PostJobServiceMapping;
use App\Models\ProviderCategoryMapping;
use App\Models\ProviderDocument;
use App\Models\ProviderPayout;
use App\Models\ProviderServiceAddressMapping;
use App\Models\ProviderType;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function dashboardDetail(Request $request)
    {
        $per_page = 6;
        $perPageService = 4;
        $currentDate = Carbon::now();

        $slider = SliderResource::collection(Slider::join('services', 'sliders.type_id', '=', 'services.id')->with(['provider', 'providerAddress'])->select('sliders.*')->paginate($per_page));

        $servicePackages = ServicePackage::where('status', 1)->orderBy('id', 'desc')->limit(5)->get();

        $servicePackages = ServicePackageResource::collection($servicePackages);

        $lastOrder = Booking::where('customer_id', $request->customer_id)->orderBy('id', 'desc')->limit(1)->get();

        $lastOrder = BookingResource::collection($lastOrder);

        $lastOrder = $lastOrder->isEmpty() ? null : $lastOrder[0];

        $category = CategoryResource::collection(Category::where('status', 1)->where('is_featured', 1)->orderBy('name', 'asc')->paginate(8));

        $service = Service::with(['commonServiceProviders', 'uploadedRequiredCertificates'])->where('status', 1)
            ->where('end_date', '>=', $currentDate->toDateString())
            ->where('user_service_status', 1)
            ->where('user_service_status', 1)->where('service_type', 'service')->with('providers', 'category', 'serviceRating');

        if ($request->has('city_id') && !empty($request->city_id)) {
            $service->whereHas('providers', function ($a) use ($request) {
                $a->where('city_id', $request->city_id);
            });
        }
        if (default_earning_type() === 'subscription') {
            $service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe', 1);
            });
        } else {
            $service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1);
            });
        }
        $commonServices = $service->where('admin_service_type', 'common')->orderBy('id', 'desc');

        $commonServices = CommonServiceResource::collection($commonServices->get());
        $service = $service->where('admin_service_type', "!=", 'common')->orderBy('id', 'desc');
        $service = ServiceResource::collection($service->paginate($perPageService));

        if (default_earning_type() === 'subscription') {
            $provider = User::where('user_type', 'provider')->where('status', 1)->where('is_subscribe', 1);
        } else {
            $provider = User::where('user_type', 'provider')->where('status', 1);
        }
        if ($request->has('city_id') && !empty($request->city_id)) {
            $provider = $provider->where('city_id', $request->city_id);
        }
        $provider = $provider->paginate($per_page);

        $provider = UserResource::collection($provider);

        $configurations = Setting::with('country')->get();

        $general_settings = AppSetting::first();
        $general_settings->site_logo = getSingleMedia(settingSession('get'), 'site_logo', null);

        $distance = 30;

        $paypal_configuration = false;

        $userLatitude = $request->latitude;
        $userLongitude = $request->longitude;

        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->latitude)) {
            $get_distance = getSettingKeyValue('DISTANCE', 'DISTANCE_RADIOUS');
            $get_unit = getSettingKeyValue('DISTANCE', 'DISTANCE_TYPE');

            $locations = Service::locationService($request->latitude, $request->longitude, $get_distance, $get_unit);

            $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id', $locations)->get()->pluck('service_id');

            $sliders = Slider::join('provider_address_mappings', 'sliders.provider_address_id', '=', 'provider_address_mappings.id')
                ->with(['provider', 'providerAddress'])
                ->select('sliders.*', 'provider_address_mappings.latitude', 'provider_address_mappings.longitude')
                ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(provider_address_mappings.latitude)) * cos(radians(provider_address_mappings.longitude) - radians(?)) + sin(radians(?)) * sin(radians(provider_address_mappings.latitude)))) AS distance', [$request->latitude, $request->longitude, $request->latitude])
                ->having('distance', '<=', $distance)
                ->orderBy('distance')
                ->get();

            $slider = SliderResource::collection($sliders);

            $services = Service::select('services.*', 'provider_address_mappings.id as address_id', 'provider_address_mappings.latitude', 'provider_address_mappings.longitude')
                ->join('users', 'services.provider_id', '=', 'users.id')
                ->join('provider_service_address_mappings', 'services.id', '=', 'provider_service_address_mappings.service_id')
                ->join('provider_address_mappings', 'provider_service_address_mappings.provider_address_id', '=', 'provider_address_mappings.id')
                ->with('providerServiceAddress')
                ->where('user_service_status', 1)
                ->where('services.status', 1)
                ->where('users.status', 1)
                ->where('end_date', '>=', $currentDate->toDateString())
                ->where('admin_service_type', '!=', 'common')
                ->selectRaw('(6371 * acos(cos(radians(' . $userLatitude . ')) * cos(radians(provider_address_mappings.latitude)) * cos(radians(provider_address_mappings.longitude) - radians(' . $userLongitude . ')) + sin(radians(' . $userLatitude . ')) * sin(radians(provider_address_mappings.latitude)))) AS distance');

            if (default_earning_type() === 'subscription') {
                $services->where('is_subscribe', 1);
            }

            $services = $services->having('distance', '<=', $distance)->groupBy('services.id')
                ->orderBy('distance')
                ->limit($perPageService)
                ->get();

//            foreach ($services as $key => $value) {
//                $destinationLat = $value->latitude;
//                $destinationLng = $value->longitude;
//                $accurateDistance = googleMatrixApi($userLatitude, $userLongitude, $destinationLat, $destinationLng, $value->distance);
//
//                $value->distance =  $accurateDistance;
//            }

            $commonServices = Service::select('services.*', 'provider_address_mappings.id as address_id', 'provider_address_mappings.latitude', 'provider_address_mappings.longitude')
                ->join('provider_service_address_mappings', 'services.id', '=', 'provider_service_address_mappings.service_id')
                ->join('provider_address_mappings', 'provider_service_address_mappings.provider_address_id', '=', 'provider_address_mappings.id')
                ->with('providerServiceAddress')
                ->where('user_service_status', 1)
                ->where('services.status', 1)
                ->where('end_date', '>=', $currentDate->toDateString())
                ->where('admin_service_type', 'common')
                ->selectRaw('(6371 * acos(cos(radians(' . $userLatitude . ')) * cos(radians(provider_address_mappings.latitude)) * cos(radians(provider_address_mappings.longitude) - radians(' . $userLongitude . ')) + sin(radians(' . $userLatitude . ')) * sin(radians(provider_address_mappings.latitude)))) AS distance')
                ->having('distance', '<=', $distance)
                ->groupBy('services.id')
                ->orderBy('distance')
                ->limit($perPageService)
                ->get();

//            foreach ($commonServices as $key => $value) {
//                $destinationLat = $value->latitude;
//                $destinationLng = $value->longitude;
//                $accurateDistance = googleMatrixApi($userLatitude, $userLongitude, $destinationLat, $destinationLng, $value->distance);
//
//                $value['distance'] = $accurateDistance;
//            }
            $service = ServiceResource::collection($services);
            $commonServices = CommonServiceResource::collection($commonServices);
        }
        $privacy_policy = Setting::where('type', 'privacy_policy')->where('key', 'privacy_policy')->first();

        $term_conditions = Setting::where('type', 'terms_condition')->where('key', 'terms_condition')->first();

        $app_download = AppDownload::first();

        if ($app_download != null) {
            $app_download = new AppDownloadResource(AppDownload::first());
        }

        $payment_settings = PaymentGateway::where('status', 1)->where('type', '!=', 'razorPayX')->get();

        $payment_settings = PaymentGatewayResource::collection($payment_settings);

        $featured_service = Service::where('status', 1)->where('user_service_status', 1)->where('end_date', '>=', $currentDate->toDateString())->where('is_featured', 1)->where('service_type', 'service');
        if (default_earning_type() === 'subscription') {
            $featured_service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe', 1);
            });
        } else {
            $featured_service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1);
            });
        }
        $featured_service = $featured_service->orderBy('id', 'desc')->paginate($per_page);

        $featured_service = ServiceResource::collection($featured_service);
        if ($request->has('latitude') && !empty($request->latitude) && $request->has('longitude') && !empty($request->longitude)) {
            $featured_service = Service::select('services.*', 'provider_address_mappings.id as address_id', 'provider_address_mappings.latitude', 'provider_address_mappings.longitude')
                ->join('provider_service_address_mappings', 'services.id', '=', 'provider_service_address_mappings.service_id')
                ->join('provider_address_mappings', 'provider_service_address_mappings.provider_address_id', '=', 'provider_address_mappings.id')
                ->with('providerServiceAddress')
                ->where('user_service_status', 1)
                ->where('services.status', 1)
                ->where('end_date', '>=', $currentDate->toDateString())
                ->where('is_featured', 1)
                ->having('distance', '<=', $distance)
                ->selectRaw('(6371 * acos(cos(radians(' . $userLatitude . ')) * cos(radians(provider_address_mappings.latitude)) * cos(radians(provider_address_mappings.longitude) - radians(' . $userLongitude . ')) + sin(radians(' . $userLatitude . ')) * sin(radians(provider_address_mappings.latitude)))) AS distance')
                ->groupBy('services.id')
                ->orderBy('distance')
                ->get();

//            foreach ($featured_service as $key => $value) {
//                $destinationLat = $value->latitude;
//                $destinationLng = $value->longitude;
//                $accurateDistance = googleMatrixApi($userLatitude, $userLongitude, $destinationLat, $destinationLng, $value->distance);
//                $value['distance'] = $accurateDistance;
//            }
            $featured_service = ServiceResource::collection($featured_service);
        }
        $discount_service = Service::where('discount', '>', 0)->where('service_type', 'service');
        if (default_earning_type() === 'subscription') {
            $discount_service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1)->where('is_subscribe', 1);
            });
        } else {
            $discount_service->whereHas('providers', function ($a) use ($request) {
                $a->where('status', 1);
            });
        }
        $discount_service = $discount_service->orderBy('discount', 'desc')->paginate($per_page);

        $discount_service = ServiceResource::collection($discount_service);

        $top_rated_service = BookingRatingResource::collection(BookingRating::orderBy('rating', 'desc')->limit(5)->get());


        $customer_review = null;

        $notification = 0;
        if ($request->has('customer_id') && isset($request->customer_id)) {
            $customer_review = BookingRating::with('customer', 'service')->where('customer_id', $request->customer_id)->get();
            if (!empty($customer_review)) {
                $customer_review = BookingRatingResource::collection($customer_review);
            }
            $user = User::where('id', $request->customer_id)->first();
            $notification = empty($user) ? 0 : count($user->unreadNotifications);
        }
        $language_option = settingSession('get')->language_option;
        $language_array = languagesArray($language_option)->toArray();
        foreach ($language_array as &$value) {
            $value['flag_image'] = file_exists(public_path('/images/flags/' . $value['id'] . '.png')) ? asset('/images/flags/' . $value['id'] . '.png') : asset('/images/language.png');
        }
        $help_support = Setting::where('type', 'help_support')->where('key', 'help_support')->first();
        $refund_policy = Setting::where('type', 'refund_cancellation_policy')->where('key', 'refund_cancellation_policy')->first();
        $upcomming_booking = Booking::where('customer_id', $request->customer_id)->with('customer')->where('status', 'accept')->orderBy('id', 'DESC')->take(5)->get();
        if (!empty($upcomming_booking)) {
            $upcomming_booking = BookingResource::collection($upcomming_booking);
        }
        $is_advanced_allowed = Setting::where('type', '=', 'ADVANCED_PAYMENT_SETTING')->first();
        if ($is_advanced_allowed !== null) {
            $is_advanced_allowed = $is_advanced_allowed->value;
        }
        // $is_digital_service_allowed = Setting::where('type','=','DIGITAL_SERVICE_SETTING')->first();
        // if($is_digital_service_allowed !== null){
        //     $is_digital_service_allowed = $is_digital_service_allowed->value;
        // }
        $blogs = Blog::paginate($per_page);
        $blogs = BlogResource::collection($blogs);

        $departments = Department::where('status', 1)->get();
        $departments = DepartmentResource::collection($departments);

        $enable_user_wallet = Setting::where('type', '=', 'USER_WALLET_SETTING')->first();
        if ($enable_user_wallet !== null) {
            $enable_user_wallet = $enable_user_wallet->value;
        }

        $sentRequests = getTotalRequests($request->customer_id);
        $expiredRequests = getTotalExpiredRequests($request->customer_id);
        $placedWorkOrders = getTotalAcceptedOrders($request->customer_id);

        $response = [
            'status' => true,
            'featured_service' => $featured_service,
            'service' => $service,
            'last_order' => $lastOrder,
            'sent_requests' => $sentRequests,
            'expired_requests' => $expiredRequests,
            'placed_work_orders' => $placedWorkOrders,
            'departments' => $departments,
            'common_services' => $commonServices,
            'service_packages' => $servicePackages,
            'slider' => $slider,
            'category' => $category,
            'provider' => $provider,
            'configurations' => $configurations,
            'generalsetting' => $general_settings,
            'privacy_policy' => $privacy_policy,
            'term_conditions' => $term_conditions,
            'help_support' => $help_support,
            'refund_policy' => $refund_policy,
            'payment_settings' => $payment_settings,
            'customer_review' => $customer_review,
            'notification_unread_count' => $notification,
            'discount_service' => $discount_service,
            'top_rated_service' => $top_rated_service,
            'helpline_number' => $general_settings->helpline_number,
            'inquriy_email' => $general_settings->inquriy_email,
            'language_option' => $language_array,
            'app_download' => !empty($app_download) ? $app_download : null,
            'upcomming_booking' => $upcomming_booking,
            'is_advanced_payment_allowed' => $is_advanced_allowed,
            'blogs' => $blogs,
            'enable_user_wallet' => $enable_user_wallet
        ];

        return comman_custom_response($response);
    }

    public function providerDashboard(Request $request)
    {
        $data = $request->all();
        $radius = !empty($data['radius']) ? $data['radius'] : getSettingKeyValue('DISTANCE', 'DISTANCE_RADIOUS');

        $userId = auth()->user()->id;

        $postJobRequests = $bookingLocations = [];

        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $latitude = $data['latitude'];
            $longitude = $data['longitude'];

            $subcategoryIds = ProviderCategoryMapping::where('provider_id', getLoggedUserId())->pluck('sub_category_id');

            $serviceIds = [];

            if (!empty($subcategoryIds)) {
                $serviceIds = Service::whereIn('subcategory_id', $subcategoryIds)->pluck('id');
            }

            $postJobIds = [];

            if (!empty($serviceIds))
                $postJobIds = PostJobServiceMapping::whereIn('service_id', $serviceIds)->pluck('post_request_id');

            $postJobRequests = PostJobRequest::selectRaw("id, provider_id, address, latitude, longitude,
                ( 6371 * acos( cos( radians($latitude) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians($longitude)
                ) + sin( radians($latitude) ) *
                sin( radians( latitude ) ) )
                ) AS distance")
                ->having("distance", "<=", $radius)
                ->where('status', '!=', 'assigned')
                ->where('created_at', '>', auth()->user()->created_at)
                ->where('expired_at', '>=', Carbon::now()->toDateString())
                // Below condition for remove post job if current provider placed the bid
                ->whereDoesntHave('postBidList', function ($query) {
                    return $query->where('post_job_bids.provider_id', auth()->user()->id)
                        ->whereColumn('post_request_id', 'post_job_requests.id');
                })
                ->whereIn('id', $postJobIds)
//                ->whereHas('postServiceMapping', function ($query) use ($serviceIds) {
//                    return $query->whereIn('service_id', $serviceIds);
//                })
                ->orderBy("distance", 'asc')
                ->get();

            $bookingLocations = Booking::selectRaw("id, address, latitude, longitude,
                ( 6371 * acos( cos( radians($latitude) ) *
                cos( radians( latitude ) )
                * cos( radians( longitude ) - radians($longitude)
                ) + sin( radians($latitude) ) *
                sin( radians( latitude ) ) )
                ) AS distance")
                ->where('date', '>=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('provider_id', $userId)
                ->where('status', 'pending')
                ->having("distance", "<=", $radius)
                ->orderBy("distance", 'asc')
                ->get();
        }

        $provider = User::with(['department', 'providerCategoryMapping', 'providerCurrentBadgeMapping.badge'])->find($userId);

        $providerDepartment = Department::with(['requiredDepartmentCertificates'])->where('id', $provider->department_id)->first();

        $requireDocumentIds = [];

        if (!empty($providerDepartment)) {
            if (!empty($providerDepartment->requiredDepartmentCertificates)) {
                foreach ($providerDepartment->requiredDepartmentCertificates as $requiredDepartmentCertificate) {
                    $requireDocumentIds[] = $requiredDepartmentCertificate->id;
                }
            }
        }

        $providerDocumentVerified = true;

        if (!empty($requireDocumentIds)) {
            $providerDocumentCount = ProviderDocument::whereIn('document_id', $requireDocumentIds)->where('is_verified', 1)->get()->count();

            $providerDocumentVerified = false;

            if (count($requireDocumentIds) == $providerDocumentCount) {
                $providerDocumentVerified = true;
            }
        }

        $per_page = config('constant.PER_PAGE_LIMIT');

        $total_booking = Booking::leftJoin('booking_provider_mappings', 'booking_provider_mappings.booking_id', 'bookings.id')
            ->where('booking_provider_mappings.provider_id', $userId)->count();

        $service = Service::with(['departmentType', 'materialUnit'])->myService();
        $total_service = $service->get()->count();

        if ($request->has('city_id') && !empty($request->city_id)) {
            $service->whereHas('providers', function ($a) use ($request) {
                $a->where('city_id', $request->city_id);
            });
        }

        $commonServices = $service;

        $service = $service->where('admin_service_type', '!=', 'common')->orderBy('services.id', 'desc')->paginate($per_page);

        $service = ServiceResource::collection($service);

        $commonServices = $commonServices->where('admin_service_type', 'common')->limit(2)->get();
        $commonServices = CommonServiceResource::collection($commonServices);

        $category = CategoryResource::collection(Category::orderBy('name', 'asc')->paginate($per_page));

        $handyman = User::myUsers();
        $handyman = $handyman->paginate($per_page);

        $handyman = UserResource::collection($handyman);


        $providerEarning = ProviderPayout::where('provider_id', $provider->id)->sum('amount') ?? 0;

        $revenuedata = ProviderPayout::selectRaw('sum(amount) as total , DATE_FORMAT(created_at , "%m") as month')
            ->where('provider_id', $userId)
            ->whereYear('created_at', date('Y'))
            ->groupBy('month');
        $revenuedata = $revenuedata->get();
        $data['revenueData'] = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueData = 0;
            foreach ($revenuedata as $revenue) {
                if ((int)$revenue['month'] == $i) {

                    $data['revenueData'][] = [
                        $i => (int)$revenue['total']
                    ];
                    $revenueData++;
                }
            }
            if ($revenueData == 0) {
                $data['revenueData'][] = (object)[];
            }
        }
        $configurations = Setting::with('country')->get();
        $commission = ProviderType::where('id', $provider->providertype_id)->first();
        $notification = count($provider->unreadNotifications);
        $active_plan = get_user_active_plan($provider->id);
        if (is_any_plan_active($provider->id) == 0 && is_subscribed_user($provider->id) == 0) {
            $active_plan = user_last_plan($provider->id);
        }
        $payment_settings = PaymentGatewayResource::collection(PaymentGateway::where('status', 1)->get());

        $get_earning_type = default_earning_type();
        $provider_wallet = Wallet::where('user_id', $provider->id)->where('status', 1)->first();
        $privacy_policy = Setting::where('type', 'privacy_policy')->where('key', 'privacy_policy')->first();

        $term_conditions = Setting::where('type', 'terms_condition')->where('key', 'terms_condition')->first();
        $general_settings = AppSetting::first();
        $language_option = settingSession('get')->language_option;
        $language_array = languagesArray($language_option)->toArray();
        foreach ($language_array as &$value) {
            $value['flag_image'] = file_exists(public_path('/images/flags/' . $value['id'] . '.png')) ? asset('/images/flags/' . $value['id'] . '.png') : asset('/images/language.png');
        }
        $online_handyman = User::myUsers()->where('is_available', 1)->orderBy('last_online_time', 'desc')->limit(10)->get();
        $profile_array = [];
        if (!empty($online_handyman)) {
            foreach ($online_handyman as $online) {
                $profile_array[] = $online->login_type !== null ? $online->social_image : getSingleMedia($online, 'profile_image', null);
            }
        }
        $post_request = PostJobRequest::where('status', 'requested')->latest()->take(5)->get();
        $post_requests = PostJobRequestResource::collection($post_request);
        $app_download = null;
        $app_download = AppDownload::first();
        if ($app_download != null) {
            $app_download = new AppDownloadResource(AppDownload::first());
        }
        $upcomming_booking = Booking::myBooking()->with('customer')->where('date', '>', now())->orderBy('id', 'DESC')->take(5)->get();

        if (!empty($upcomming_booking)) {
            $upcomming_booking = BookingResource::collection($upcomming_booking);
        }
        $is_advanced_allowed = Setting::where('type', '=', 'ADVANCED_PAYMENT_SETTING')->first();
        if ($is_advanced_allowed !== null) {
            $is_advanced_allowed = $is_advanced_allowed->value;
        }
        // $is_digital_service_allowed = Setting::where('type','=','DIGITAL_SERVICE_SETTING')->first();
        // if($is_digital_service_allowed !== null){
        //     $is_digital_service_allowed = $is_digital_service_allowed->value;
        // }

        $categoriesIds = $provider->providerCategoryMapping()->pluck('category_id');
        $subCategoriesIds = $provider->providerCategoryMapping()->pluck('sub_category_id');
        $categories = Category::whereIn('id', $categoriesIds)->get();
        $subCategories = SubCategory::whereIn('id', $subCategoriesIds)->get();

        $serviceIds = $provider->providerServiceMapping()->pluck('service_id');

        $providerRatings = 0;

        if (count($serviceIds) > 0) {
            $bookingRatings = BookingRating::whereIn('service_id', $serviceIds)->pluck('rating');

            if (count($bookingRatings) > 0) {
                $totalRatings = 0;

                foreach ($bookingRatings as $bookingRating) {
                    $totalRatings += $bookingRating;
                }

                $providerRatings = $totalRatings / count($bookingRatings);
            }
        }

        $totalRequests = PostJobRequest::myPostJob()->whereIn('post_job_requests.status', ['requested', 'accepted', 'assigned'])->get()->count();

        $expiredRequests = PostJobRequest::myPostJob()->where('expired_at', '<=', date('Y-m-d'))->get()->count();

        $respondedRequests = PostJobRequest::with('postBidList')->whereHas('postBidList', function ($q) use ($userId) {
            return $q->where('provider_id', $userId);
        })->where('created_at', ">=", auth()->user()->created_at)->count();

        $acceptedBookings = Booking::leftJoin('booking_provider_mappings', 'booking_provider_mappings.booking_id', 'bookings.id')
            ->where('booking_provider_mappings.provider_id', $userId)
            ->where('status', 'accept')
            ->get()
            ->count();

        $currentBooking = Booking::where('provider_id', $userId)
            ->where('status', 'pending')
            ->where('date', '>=', Carbon::now()->toDateString())
            ->orderBy('id', 'desc')
            ->limit(1)
            ->get();

        $rejectedBookings = Booking::leftJoin('booking_provider_mappings', 'booking_provider_mappings.booking_id', 'bookings.id')
            ->where('booking_provider_mappings.provider_id', $userId)
            ->where('status', 'rejected')
            ->count();

        $response = [
            'status' => true,
            'booking_locations' => $bookingLocations,
            'total_requests' => $totalRequests,
            'provider_document_verified' => $providerDocumentVerified,
            'current_booking' => BookingResource::collection($currentBooking),
            'responded_requests' => $respondedRequests,
            'accepted_orders' => $acceptedBookings,
            'rejected_orders' => $rejectedBookings,
            'expired_requests' => $expiredRequests,
            'provider_grade' => !empty($provider->provider_grade) ? $provider->provider_grade : "",
            'current_badge' => (!empty($provider->providerCurrentBadgeMapping) && !empty($provider->providerCurrentBadgeMapping->badge)) ? $provider->providerCurrentBadgeMapping->badge : Badges::first(),
            'post_job_requests' => $postJobRequests,
            'provider_id' => $provider->id,
            'member_since' => $provider->created_at,
            'provider_rating' => (float)$providerRatings,
            'department' => (!empty($provider->department)) ? $provider->department->name : "-",
            'categories' => $categories,
            'sub_categories' => $subCategories,
            'total_booking' => $total_booking,
            'total_service' => $total_service,
            'total_handyman' => $handyman->count(),
            'common_services' => $commonServices,
            'today_cash' => today_cash_total($userId, Carbon::today(), Carbon::today()),
            'service' => $service,
            'category' => $category,
            'handyman' => $handyman,
            'total_revenue' => (float)$providerEarning,
            'monthly_revenue' => $data,
            'configurations' => $configurations,
            'commission' => $commission,
            'notification_unread_count' => $notification,
            'subscription' => $active_plan,
            'is_subscribed' => is_subscribed_user($provider->id),
            'payment_settings' => $payment_settings,
            'earning_type' => $get_earning_type,
            'provider_wallet' => $provider_wallet,
            'helpline_number' => $general_settings->helpline_number,
            'inquriy_email' => $general_settings->inquriy_email,
            'privacy_policy' => $privacy_policy,
            'term_conditions' => $term_conditions,
            'language_option' => $language_array,
            'online_handyman' => $profile_array,
            'post_requests' => $post_requests,
            'app_download' => $app_download,
            'upcomming_booking' => $upcomming_booking,
            'is_advanced_payment_allowed' => $is_advanced_allowed,
            'today_cash' => today_cash_total($userId, date('Y-m-d'), date('Y-m-d')),
        ];

        return comman_custom_response($response);

    }

    public function handymanDashboard(Request $request)
    {
        $handyman = User::find(auth()->user()->id);
        if ($handyman) {
            $admin = AppSetting::first();
            date_default_timezone_set($admin->time_zone ?? 'UTC');
            $get_current_time = Carbon::now();
            $handyman->last_online_time = $get_current_time->toTimeString();
            $handyman->update();
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        $booking = BookingHandymanMapping::with('bookings')->where('handyman_id', auth()->user()->id)->get();
        $upcomming = BookingHandymanMapping::with('bookings')->whereHas('bookings', function ($bookings) {
            $bookings->where('status', 'accept');
        })->where('handyman_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        $today_booking = BookingHandymanMapping::with('bookings')->whereHas('bookings', function ($bookings) {
            $bookings->whereDate('date', Carbon::today());
        })->where('handyman_id', auth()->user()->id)->get();
        $completed_booking = BookingHandymanMapping::with('bookings')->whereHas('bookings', function ($bookings) {
            $bookings->where('status', 'completed');
        })->where('handyman_id', auth()->user()->id)->orderBy('id', 'DESC')->get();
        $handyman_rating = HandymanRating::where('handyman_id', auth()->user()->id)->orderBy('id', 'desc')->paginate(10);
        $handyman_rating = HandymanRatingResource::collection($handyman_rating);
        $commission = HandymanType::where('id', $handyman->handymantype_id)->first();
        $handymanEarning = HandymanPayout::where('handyman_id', auth()->user()->id)->sum('amount') ?? 0;

        $revenuedata = HandymanPayout::selectRaw('sum(amount) as total , DATE_FORMAT(created_at , "%m") as month')
            ->where('handyman_id', auth()->user()->id)
            ->whereYear('created_at', date('Y'))
            ->groupBy('month');
        $revenuedata = $revenuedata->get();
        $data['revenueData'] = [];
        for ($i = 1; $i <= 12; $i++) {
            $revenueData = 0;
            foreach ($revenuedata as $revenue) {
                if ((int)$revenue['month'] == $i) {

                    $data['revenueData'][] = [
                        $i => (int)$revenue['total']
                    ];
                    $revenueData++;
                }
            }
            if ($revenueData == 0) {
                $data['revenueData'][] = (object)[];
            }
        }

        $notification = count($handyman->unreadNotifications);
        $configurations = Setting::with('country')->get();
        $payment_settings = PaymentGatewayResource::collection(PaymentGateway::where('status', 1)->get());
        $privacy_policy = Setting::where('type', 'privacy_policy')->where('key', 'privacy_policy')->first();

        $term_conditions = Setting::where('type', 'terms_condition')->where('key', 'terms_condition')->first();
        $general_settings = AppSetting::first();
        $language_option = settingSession('get')->language_option;
        $language_array = languagesArray($language_option)->toArray();
        foreach ($language_array as &$value) {
            $value['flag_image'] = file_exists(public_path('/images/flags/' . $value['id'] . '.png')) ? asset('/images/flags/' . $value['id'] . '.png') : asset('/images/language.png');
        }
        $upcomming_booking = Booking::myBooking()->with('customer')->orderBy('id', 'DESC')->take(5)->get();
        if (!empty($upcomming_booking)) {
            $upcomming_booking = BookingResource::collection($upcomming_booking);
        }
        // $is_digital_service_allowed = Setting::where('type','=','DIGITAL_SERVICE_SETTING')->first();
        // if($is_digital_service_allowed !== null){
        //     $is_digital_service_allowed = $is_digital_service_allowed->value;
        // }
        $service = Service::myService()->where('status', 1)
            ;
        $total_service = $service->count();
        $service = $service->orderBy('id', 'desc')->paginate($per_page);
        $service = ServiceResource::collection($service);
        $response = [
            'status' => true,
            'today_cash' => today_cash_total(auth()->user()->id, Carbon::today(), Carbon::today()),
            'total_booking' => $booking->count(),
            'upcomming_booking' => $upcomming->count(),
            'today_booking' => $today_booking->count(),
            'commission' => $commission,
            'handyman_reviews' => $handyman_rating,
            'total_revenue' => $handymanEarning,
            'monthly_revenue' => $data,
            'notification_unread_count' => $notification,
            'configurations' => $configurations,
            'payment_settings' => $payment_settings,
            'helpline_number' => $general_settings->helpline_number,
            'inquriy_email' => $general_settings->inquriy_email,
            'privacy_policy' => $privacy_policy,
            'term_conditions' => $term_conditions,
            'language_option' => $language_array,
            'isHandymanAvailable' => $handyman->is_available,
            'completed_booking' => $completed_booking->count(),
            'upcomming_booking' => $upcomming_booking,
            'service' => $service,
            //'is_digital_service_allowed'    => $is_digital_service_allowed,
        ];
        return comman_custom_response($response);

    }

    public function adminDashboard(Request $request)
    {
        $admin = User::find(auth()->user()->id);
        $configurations = Setting::with('country')->get();
        $notification = count($admin->unreadNotifications);
        $general_settings = AppSetting::first();
        $privacy_policy = Setting::where('type', 'privacy_policy')->where('key', 'privacy_policy')->first();

        $term_conditions = Setting::where('type', 'terms_condition')->where('key', 'terms_condition')->first();
        $general_settings = AppSetting::first();
        $language_option = settingSession('get')->language_option;
        $language_array = languagesArray($language_option)->toArray();
        foreach ($language_array as &$value) {
            $value['flag_image'] = file_exists(public_path('/images/flags/' . $value['id'] . '.png')) ? asset('/images/flags/' . $value['id'] . '.png') : asset('/images/language.png');
        }
        $services = Booking::with('categoryService')->myBooking()->showServiceCount()->take(5)->get();
        $post_request = PostJobRequest::latest()->take(5)->get();
        $post_requests = PostJobRequestResource::collection($post_request);
        $is_advanced_allowed = Setting::where('type', '=', 'ADVANCED_PAYMENT_SETTING')->first();
        if ($is_advanced_allowed !== null) {
            $is_advanced_allowed = $is_advanced_allowed->value;
        }
        // $is_digital_service_allowed = Setting::where('type','=','DIGITAL_SERVICE_SETTING')->first();
        // if($is_digital_service_allowed !== null){
        //     $is_digital_service_allowed = $is_digital_service_allowed->value;
        // }

        $response = [
            'status' => true,
            'total_booking' => Booking::myBooking()->count(),
            'total_service' => Service::myService()->count(),
            'total_provider' => User::myUsers('get_provider')->count(),
            'total_revenue' => Payment::where('payment_status', 'paid')->sum('total_amount'),
            'monthly_revenue' => adminEarning(),
            // 'service'                       => new ServiceResource($services->categoryService),
            'provider' => UserResource::collection(User::myUsers('get_provider')->orderBy('id', 'DESC')->take(5)->get()),
            'user' => UserResource::collection(User::myUsers('get_customer')->orderBy('id', 'DESC')->take(5)->get()),
            'upcomming_booking' => BookingResource::collection(Booking::myBooking()->where('status', 'pending')->orderBy('id', 'DESC')->take(5)->get()),
            'configurations' => $configurations,
            'notification_unread_count' => $notification,
            'helpline_number' => $general_settings->helpline_number,
            'inquriy_email' => $general_settings->inquriy_email,
            'privacy_policy' => $privacy_policy,
            'term_conditions' => $term_conditions,
            'language_option' => $language_array,
            'earning_type' => default_earning_type(),
            'post_requests' => $post_requests,
            'is_advanced_payment_allowed' => $is_advanced_allowed,
            //'is_digital_service_allowed'    => $is_digital_service_allowed,

        ];

        return comman_custom_response($response);
    }

    public function configurations(Request $request)
    {

        $user = User::find(auth()->user()->id);

        $configurations = Setting::with('country')->get();

        $notification = 0;
        if ($request->has('customer_id') && isset($request->customer_id)) {
            $customer_review = BookingRating::with('customer', 'service')->where('customer_id', $request->customer_id)->get();
            if (!empty($customer_review)) {
                $customer_review = BookingRatingResource::collection($customer_review);
            }
            $user = User::where('id', $request->customer_id)->first();
            $notification = count($user->unreadNotifications);
        }

        $active_plan = get_user_active_plan($user->id);
        if (is_any_plan_active($user->id) == 0 && is_subscribed_user($user->id) == 0) {
            $active_plan = user_last_plan($user->id);
        }

        $payment_settings = PaymentGateway::where('status', 1)->where('type', '!=', 'razorPayX')->get();

        $payment_settings = PaymentGatewayResource::collection($payment_settings);

        $other_setting = Setting::where('type', 'OTHER_SETTING')->where('key', 'OTHER_SETTING')->first();

        $general_settings = AppSetting::first();

        $privacy_policy = Setting::where('type', 'privacy_policy')->where('key', 'privacy_policy')->first();

        $term_conditions = Setting::where('type', 'terms_condition')->where('key', 'terms_condition')->first();

        $language_option = settingSession('get')->language_option;
        $language_array = languagesArray($language_option)->toArray();
        foreach ($language_array as &$value) {
            $value['flag_image'] = file_exists(public_path('/images/flags/' . $value['id'] . '.png')) ? asset('/images/flags/' . $value['id'] . '.png') : asset('/images/language.png');
        }

        $app_download = null;
        $app_download = AppDownload::first();
        if ($app_download != null) {
            $app_download = new AppDownloadResource(AppDownload::first());

        }

        $is_advanced_allowed = Setting::where('type', '=', 'ADVANCED_PAYMENT_SETTING')->first();
        if ($is_advanced_allowed !== null) {
            $is_advanced_allowed = $is_advanced_allowed->value;
        }

        $enable_user_wallet = Setting::where('type', '=', 'USER_WALLET_SETTING')->first();
        if ($enable_user_wallet !== null) {
            $enable_user_wallet = $enable_user_wallet->value;
        }

        $other_data = !empty($other_setting) ? json_decode($other_setting->value) : null;
        if ($other_data !== null) {
            $is_advanced_allowed = $other_data->advanced_payment_setting;
        }

        if ($other_data !== null) {
            $enable_user_wallet = $other_data->wallet;
        }
        $general_settings = AppSetting::getAppSettings()->first();
        $general_settings->site_logo = getSingleMedia(settingSession('get'), 'site_logo', null);

        if ($request->has('is_authenticated') && $request->is_authenticated == 0) {

            $response = [

                'other_settings' => $other_setting ? json_decode($other_setting->value) : null,

            ];

        } else {

            $response = [
                'configurations' => $configurations,
                'notification_unread_count' => $notification,
                'subscription' => $active_plan,
                'is_subscribed' => is_subscribed_user($user->id),
                'payment_settings' => $payment_settings,
                'other_settings' => $other_setting ? json_decode($other_setting->value) : null,
                'helpline_number' => $general_settings->helpline_number,
                'inquiry_email' => $general_settings->inquriy_email,
                'privacy_policy' => $privacy_policy,
                'term_conditions' => $term_conditions,
                'language_option' => $language_array,
                'app_download' => !empty($app_download) ? $app_download : null,
                'is_advanced_payment_allowed' => $is_advanced_allowed,
                'enable_user_wallet' => $enable_user_wallet,
                'general_settings' => $general_settings
            ];

        }


        return comman_custom_response($response);
    }

    public function otherConfigurations(Request $request)
    {
        $other_setting = Setting::where('type', 'OTHER_SETTING')->where('key', 'OTHER_SETTING')->first();
        $configurations = Setting::with('country')->get();
        return comman_custom_response([
            'other_settings' => $other_setting ? json_decode($other_setting->value) : null,
            'configurations' => $configurations
        ]);
    }
}
