<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendChatRequest;
use App\Http\Resources\API\BankResource;
use App\Http\Resources\API\CommonServiceResource;
use App\Http\Resources\API\CouponResource;
use App\Http\Resources\API\ProviderAddressMappingResource;
use App\Http\Resources\API\ProviderTaxResource;
use App\Http\Resources\API\ServicePackageResource;
use App\Http\Resources\API\ServiceResource;
use App\Http\Resources\API\TaxResource;
use App\Http\Resources\API\TypeResource;
use App\Models\ApiLog;
use App\Models\AppSetting;
use App\Models\Bank;
use App\Models\Booking;
use App\Models\City;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\CouponServiceMapping;
use App\Models\HandymanType;
use App\Models\PackageServiceMapping;
use App\Models\ProviderAddressMapping;
use App\Models\ProviderServiceAddressMapping;
use App\Models\ProviderTaxMapping;
use App\Models\ProviderType;
use App\Models\Service;
use App\Models\ServicePackage;
use App\Models\ServiceProviderMapping;
use App\Models\State;
use App\Models\Tax;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mail;
use PDF;
use Throwable;


class CommanController extends Controller
{
    public function getCountryList(Request $request)
    {
        $list = Country::get();

        return response()->json($list);
    }

    public function getStateList(Request $request)
    {
        $list = State::where('country_id', $request->country_id)->get();

        return response()->json($list);
    }

    public function getCityList(Request $request)
    {
        $list = City::where('state_id', $request->state_id)->get();

        return response()->json($list);
    }

    public function getProviderTax(Request $request)
    {

        $provider_id = !empty($request->provider_id) ? $request->provider_id : auth()->user()->id;
        $taxes = ProviderTaxMapping::with('taxes')->whereHas('taxes', function ($a) {
            $a->where('status', 1);
        });
        if (auth()->user() !== null) {
            if (auth()->user()->hasRole('admin')) {
                $taxes = $taxes;
            }
        } else {
            $taxes = $taxes->where('provider_id', $provider_id)->whereHas('taxes', function ($a) {
                $a->where('status', 1);
            });
        }


        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $taxes->count();
            }
        }
        $taxes = $taxes->orderBy('created_at', 'desc')->paginate($per_page);
        $items = ProviderTaxResource::collection($taxes);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function getSearchServices(Request $request)
    {
        $data = $request->all();

        $services = new Service();

        if (!empty($data['search'])) {
            $services = $services->where('name', 'like', '%' . $data['search'] . '%');
        }

        $services = $services->where('is_post_job', 0)->limit(5)->get([
            'id', 'name'
        ]);

        $response = [
            'data' => $services
        ];

        return comman_custom_response($response);
    }

    public function getSearchList(Request $request)
    {
        $currentDate = Carbon::now();
        $per_page = config('constant.PER_PAGE_LIMIT');
        $data = $request->all();

        $apiLogData = [
            'api_request' => json_encode($data),
            'api_response' => '',
            'api_name' => 'API-common-search-list'
        ];
        ApiLog::insert($apiLogData);
        $servicePackages = new ServicePackage();

        $service = Service::with(['departmentType', 'materialUnit', 'uploadedRequiredCertificates'])->whereIn('services.status', [1, 0])->where('service_type', 'service');

        if (empty($data['provider_id']) || (!empty($data['user_type']) && $data['user_type'] === 'user')) {
            $service = $service->where('end_date', '>=', $currentDate->toDateString())->with(['providers', 'category', 'serviceRating'])
                ->where('user_service_status', 1);
        }

        if (!empty($data['provider_id'])) {
            $service = $service->whereIn('provider_id', explode(',', $request->provider_id));
            $servicePackages = $servicePackages->whereIn('provider_id', explode(',', $data['provider_id']));
        }

        if (!empty($data['category_id'])) {
            $service->whereIn('category_id', explode(',', $data['category_id']));
            $servicePackages = $servicePackages->whereIn('category_id', explode(',', $data['category_id']));
        }

        if (!empty($data['subcategory_id'])) {
            $service->whereIn('subcategory_id', explode(',', $data['subcategory_id']));
            $servicePackages = $servicePackages->whereIn('subcategory_id', explode(',', $data['subcategory_id']));
        }

        if (!empty($data['is_price_min']) && !empty($data['is_price_max'])) {
            $min = (int)$data['is_price_min'];
            $max = (int)$data['is_price_max'];

            $service = $service->whereBetween('price', [$min, $max]);
            $servicePackages = $servicePackages->whereBetween('price', [$min, $max]);
        }

        if (!empty($data['search'])) {
            $service->where('name', 'like', "%{$data['search']}%");
            $servicePackages = $servicePackages->where('name', 'like', "%{$data['search']}%");
        }

        if (!empty($data['is_featured'])) {
            $service->where('is_featured', $data['is_featured']);
        }

        if (!empty($data['type'])) {
            $service->where('type', $data['type']);
        }

        if (!empty($data['provider_id'])) {
            $service->whereHas('providers', function ($a) {
                $a->where('status', 1);
            });

            $serviceIds = ServiceProviderMapping::whereIn('provider_id', explode(',', $data['provider_id']));

            if (!empty($data['only_common'])) {
                $serviceIds = $serviceIds->whereHas('services', function ($q) {
                    return $q->where('admin_service_type', 'common');
                });
            }

            $serviceIds = $serviceIds->pluck('service_id');

            if ($serviceIds->count() > 0) {
                $service = $service->whereIn('id', $serviceIds);
            }
        } else {
            if (default_earning_type() === 'subscription') {
                $service->whereHas('providers', function ($a) {
                    $a->where('status', 1)->where('is_subscribe', 1);
                });
            }

            $service = $service
                ->where('services.status', 1);
        }

        $locationCheckForServicePackage = false;

        $providerLocations = [];

        $radius = !empty($data['radius']) ? $data['radius'] : 30; // Radius in kilometers


        if (!empty($data['latitude']) && !empty($data['longitude'])) {
            $userLatitude = $data['latitude'];
            $userLongitude = $data['longitude'];

            $providerLocations = ProviderAddressMapping::select('*')
                ->selectRaw(
                    '( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance',
                    [$userLatitude, $userLongitude, $userLatitude]
                );

            if (!empty($request->radius)) {
                $providerLocations = $providerLocations->havingRaw('distance <= ?', [$request->radius]);
            }

            $providerLocations = $providerLocations->orderBy('distance', 'desc')
                ->get();

            $providerLocations = ProviderAddressMappingResource::collection($providerLocations);

            $service = $service->with('providerServiceAddress');
            if (!empty($data['provider_id'])) {
                $get_distance = (!empty($data['radius'])) ? $data['radius'] : getSettingKeyValue('DISTANCE', 'DISTANCE_RADIOUS');
                $get_unit = (!empty($data['radius'])) ? 'km' : getSettingKeyValue('DISTANCE', 'DISTANCE_TYPE');

                $locations = $service->locationService($data['latitude'], $data['longitude'], $get_distance, $get_unit);
                $service_in_location = ProviderServiceAddressMapping::whereIn('provider_address_id', $locations)->get()->pluck('service_id');
                $service = $service->whereIn('id', $service_in_location)->orderBy('services.created_at', 'desc');
            } else {
                $service = $service->join('provider_service_address_mappings', 'services.id', '=', 'provider_service_address_mappings.service_id')
                    ->join('provider_address_mappings', 'provider_service_address_mappings.provider_address_id', '=', 'provider_address_mappings.id')
                    ->select('services.*', 'provider_address_mappings.id as address_id', 'provider_address_mappings.latitude', 'provider_address_mappings.longitude')
                    ->selectRaw('(6371 * acos(cos(radians(?)) * cos(radians(provider_address_mappings.latitude)) * cos(radians(provider_address_mappings.longitude) - radians(?)) + sin(radians(?)) * sin(radians(provider_address_mappings.latitude)))) AS distance', [$data['latitude'], $data['longitude'], $data['latitude']])->having('distance', '<=', $radius);

                $locationCheckForServicePackage = true;

                $service = $service->groupBy('services.id')
                    ->orderBy('distance');
            }
        }

        if (!empty($data['is_rating'])) {
            $isRatings = array_map('floatval', explode(',', $data['is_rating']));

            $service->whereHas('serviceRating', function ($q) use ($isRatings) {
                $conditions = implode(' OR ', array_fill(0, count($isRatings), '(AVG(rating) >= ? AND AVG(rating) <= ?)'));

                $q->select('service_id', \DB::raw('AVG(rating) as average_rating'))
                    ->groupBy('service_id')
                    ->havingRaw($conditions, array_reduce($isRatings, function ($carry, $item) {
                        return array_merge($carry, [$item, $item + 0.9]);
                    }, []));
            });
        }

        if (!empty($data['only_common'])) {
            $service = $service->where('admin_service_type', 'common');
        }

        $commonServices = $featureServices = [];
        $serviceIds = [];
        foreach ($service->get() as $item) {
//           if(!empty($userLatitude) && !empty($userLongitude)){
//               $destinationLat = $item->latitude;
//               $destinationLng = $item->longitude;
//               $accurateDistance = googleMatrixApi($userLatitude, $userLongitude, $destinationLat, $destinationLng, $item->distance);
//                $item->distance =  $accurateDistance;
//           }

            $serviceIds[] = $item->id;

            if ($item->admin_service_type === 'common') {
                $commonServices[] = $item;
            }

            if ($item->is_featured == 1) {
                $featureServices[] = $item;
            }
        }

        if ($locationCheckForServicePackage && !empty($serviceIds)) {
            $servicePackageIds = PackageServiceMapping::whereIn('service_id', $serviceIds)->pluck('service_package_id');

            $servicePackages = $servicePackages->whereIn('id', $servicePackageIds);
        }

        $featureService = ServiceResource::collection($featureServices);
        $commonService = CommonServiceResource::collection($commonServices);

        $servicePackages = $servicePackages->orderBy('id', 'desc')->limit(5)->get();

        if (!empty($data['per_page'])) {
            if (is_numeric($data['per_page'])) {
                $per_page = $data['per_page'];
            }
            if ($data['per_page'] === 'all') {
                $per_page = $service->count();
            }
        }

        if (empty($data['only_common']) && empty($data['is_featured'])) {
            $service = $service->where('admin_service_type', '!=', 'common');
        } else {
            if (!empty($data['only_common']) && $data['only_common'] == true) {
                $service = $service->where('admin_service_type', 'common');
            }
        }

        $packageServices = $service;

        $packageServices = $packageServices->pluck('id');

        $service = $service->paginate($per_page);
//        foreach ($service as $key => $value) {
//            if(!empty($userLatitude) && !empty($userLongitude)){
//                $destinationLat = $value->latitude;
//                $destinationLng = $value->longitude;
//                $accurateDistance = googleMatrixApi($userLatitude, $userLongitude, $destinationLat, $destinationLng, $value->distance);
//                $value->distance =  $accurateDistance;
//            }
//        }
        $items = ServiceResource::collection($service);

        $multiplePackages = ServicePackage::where('status', 1)
            ->where('end_at', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            ->where('package_type', 'multiple');

        if (!empty($data['search'])) {
            $multiplePackages = $multiplePackages->where('name', 'like', "%{$data['search']}%");
        }

        if (!empty($data['is_price_min']) && !empty($data['is_price_max'])) {
            $min = (int)$data['is_price_min'];
            $max = (int)$data['is_price_max'];

            $multiplePackages = $multiplePackages->whereBetween('price', [$min, $max]);
        }

        if (!empty($data['provider_id'])) {
            $multiplePackages = $multiplePackages->whereIn('provider_id', explode(',', $data['provider_id']));
        }

        if (!empty($packageServices)) {
            $packageServiceIds = PackageServiceMapping::whereIn('service_id', $packageServices)->pluck('service_package_id');

            $multiplePackages = $multiplePackages->whereIn('id', $packageServiceIds);
        }

        $multiplePackages = $multiplePackages->get();

        if (!empty($servicePackages) && !empty($multiplePackages)) {
            $servicePackages = $servicePackages->merge($multiplePackages);
        }
        $servicePackages = ServicePackageResource::collection($servicePackages);
        $userServices = null;

        if (!empty($data['customer_id'])) {
            $user_service = Service::where('status', 1);
            if (!empty($data['only_common'])) {
                $user_service = $user_service->where('admin_service_type', 'common');
            }

            if (!empty($data['provider_id'])) {
                $user_service = $user_service->whereIn('provider_id', explode(',', $data['provider_id']));
            }

            $user_service = $user_service->where('added_by', $data['customer_id'])->get();
            $userServices = ServiceResource::collection($user_service);
        }

        $response = [
            'common_services' => $commonService,
            'data' => $items,
            'provider_locations' => $providerLocations,
            'service_packages' => $servicePackages,
            'feature_services' => $featureService,
            'max' => $service->max('price'),
            'min' => $service->min('price'),
            'userservices' => $userServices
        ];

        return comman_custom_response($response);
    }

    public function getTypeList(Request $request)
    {
        $user_type = !empty($request->type) ? $request->type : '';
        if(!empty($request->request_from) && $request->request_from = 'web'){
            if($user_type === 'provider'){
                $typeData = ProviderType::select('id', 'name as text')->where('status',1)->get();
            }else{
                $typeData = HandymanType::select('id', 'name as text')->where('status',1)->get();
            }
            return response()->json(['status' => 'true', 'results' => $typeData]);
        }
        else{
            if ($user_type === 'provider') {
                $typeData = ProviderType::where('status', 1);
            } else {
                $typeData = HandymanType::where('status', 1);
            }
            if (auth()->user() !== null) {
                if (auth()->user()->hasRole('admin')) {
                    if ($user_type === 'provider') {
                        $typeData = ProviderType::withTrashed();
                    } else {
                        $typeData = HandymanType::withTrashed();
                    }
                }
            }
            $per_page = config('constant.PER_PAGE_LIMIT');
            if ($request->has('per_page') && !empty($request->per_page)) {
                if (is_numeric($request->per_page)) {
                    $per_page = $request->per_page;
                }
                if ($request->per_page === 'all') {
                    $per_page = $taxes->count();
                }
            }
            $typeData = $typeData->orderBy('id', 'desc')->paginate($per_page);
            $items = TypeResource::collection($typeData);
            $response = [
                'pagination' => [
                    'total_items' => $items->total(),
                    'per_page' => $items->perPage(),
                    'currentPage' => $items->currentPage(),
                    'totalPages' => $items->lastPage(),
                    'from' => $items->firstItem(),
                    'to' => $items->lastItem(),
                    'next_page' => $items->nextPageUrl(),
                    'previous_page' => $items->previousPageUrl(),
                ],
                'data' => $items,
            ];

            return comman_custom_response($response);
        }
    }

    public function getCouponList(Request $request)
    {
        $coupondata = Coupon::withTrashed();
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $taxes->count();
            }
        }
        $coupondata = $coupondata->orderBy('id', 'desc')->paginate($per_page);
        $items = CouponResource::collection($coupondata);
        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function getCouponService(Request $request)
    {
        $servicedata = CouponServiceMapping::where('coupon_id', $request->coupon_id)->withTrashed();
        $service_id = $servicedata->pluck('service_id');
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $taxes->count();
            }
        }
        $service = Service::whereIn('id', $service_id)->orderBy('id', 'desc')->paginate($per_page);
        $items = ServiceResource::collection($service);
        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($items);
    }

    public function downloadInvoice(Request $request)
    {
        $email = $request->email;
        $booking_id = $request->booking_id;

        $bookingdata = Booking::with('handymanAdded', 'payment', 'bookingExtraCharge')->where('id', $booking_id)->first();

        $emailData['email'] = $request->email;
        $emailData['title'] = env('APP_NAME');
        $emailData['body'] = __('messages.invoice_mail_body', ['booking_id' => $booking_id]);
        $data = AppSetting::first();
        $pdf = PDF::loadView('booking.invoice', ['bookingdata' => $bookingdata, 'data' => $data]);
        try {
            Mail::send('booking.invoice_email', $emailData, function ($message) use ($data, $pdf, $emailData, $booking_id) {
                $message->to($emailData['email'])
                    ->subject($emailData['title'])
                    ->attachData($pdf->output(), 'invoice_' . $booking_id . '.pdf');
            });

            $messagedata = __('messages.send_invoice');
            return comman_message_response($messagedata);
        } catch (Throwable $th) {
            $messagedata = __('messages.something_wrong');
            return comman_message_response($messagedata);
        }

    }

    public function getBankList(Request $request)
    {
        $user_id = $request->user_id;
        $banks = Bank::where('provider_id', $user_id)->where('status', 1);
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $banks->count();
            }
        }

        $banks = $banks->paginate($per_page);
        $items = BankResource::collection($banks);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function getTaxList(Request $request)
    {

        $taxes = new Tax();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $taxes->count();
            }
        }
        $taxes = $taxes->orderBy('created_at', 'desc')->paginate($per_page);
        $items = TaxResource::collection($taxes);

        $response = [
            'pagination' => [
                'total_items' => $items->total(),
                'per_page' => $items->perPage(),
                'currentPage' => $items->currentPage(),
                'totalPages' => $items->lastPage(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'next_page' => $items->nextPageUrl(),
                'previous_page' => $items->previousPageUrl(),
            ],
            'data' => $items,
        ];

        return comman_custom_response($response);
    }

    public function taxList()
    {
        $taxes = Tax::where('status', 1)->get();

        return comman_custom_response([
            'data' => $taxes
        ]);
    }

    public function sendChatNotification(SendChatRequest $request)
    {
        $data = $request->all();

        $user = User::where('id', $data['receiver_id'])->first();

        if (!empty($user)) {
            $type = 'new_Chat_Message';

            $notificationData = [
                'id' => auth()->user()->id,
                'type' => $type,
                'subject' => $type,
                'user' => [
                    "id" => auth()->user()->id,
                    "first_name" => auth()->user()->first_name,
                    "email" => auth()->user()->email,
                    "contact" => auth()->user()->contact_number,
                    "login_type" => auth()->user()->login_type,
                    "user_type" => auth()->user()->user_type,
                ],
                'message' => auth()->user()->first_name . " has been send message in chat box",
            ];

            notificationSend($user, $type, $notificationData);

        }
        return comman_message_response("Chat notification send successfully");
    }
}
