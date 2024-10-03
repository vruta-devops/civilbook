<?php

namespace App\Http\Controllers;

use App\Models\AppDownload;
use App\Models\AppSetting;
use App\Models\Booking;
use App\Models\Category;
use App\Models\HandymanPayout;
use App\Models\Payment;
use App\Models\ProviderCategoryMapping;
use App\Models\ProviderDocument;
use App\Models\ProviderPayout;
use App\Models\Service;
use App\Models\ServiceAddon;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletWithdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        if (request()->ajax()) {
            $start = (!empty($_GET["start"])) ? date('Y-m-d', strtotime($_GET["start"])) : ('');
            $end = (!empty($_GET["end"])) ? date('Y-m-d', strtotime($_GET["end"])) : ('');
            $data =  Booking::myBooking()->where('status', 'pending')->whereDate('date', '>=', $start)->whereDate('date',   '<=', $end)->with('service')->get();
            return response()->json($data);
        }
        $setting_data = Setting::select('value')->where('type', 'dashboard_setting')->where('key', 'dashboard_setting')->first();
        $data['dashboard_setting']  =   !empty($setting_data) ? json_decode($setting_data->value) : [];
        $provider_setting_data = Setting::select('value')->where('type', 'provider_dashboard_setting')->where('key', 'provider_dashboard_setting')->first();
        $data['provider_dashboard_setting']  =  !empty($provider_setting_data) ? json_decode($provider_setting_data->value) : [];
        $handyman_setting_data = Setting::select('value')->where('type', 'handyman_dashboard_setting')->where('key', 'handyman_dashboard_setting')->first();
        $data['handyman_dashboard_setting']  =   !empty($handyman_setting_data) ? json_decode($handyman_setting_data->value) : [];

        $totalService = Service::query()->myService()->whereHas('addedBy', function ($query) {
            $query->where('user_type', '!=', 'user');
        });

        if (auth()->user()->hasAnyRole(['admin']) && !empty($query)) {
            $totalService = $totalService->where('service_type', 'service')->withTrashed();
        }
        $totalService = $totalService->where('user_service_status', '!=', 0)->get()->count();

        $data['dashboard'] = [
            'count_total_booking'               => Booking::myBooking()->count(),
            'count_total_service' => $totalService,
            'count_total_provider'              => User::myUsers('get_provider')->count(),
            'new_customer'                      => User::myUsers('get_customer')->where('user_type', 'user')->orderBy('id', 'DESC')->take(5)->get(),
            'new_provider'                      => User::myUsers('get_provider')->with('getServiceRating')->orderBy('id', 'DESC')->take(5)->get(),
            'upcomming_booking'                 => Booking::myBooking()->with('customer')->where('status', 'pending')->orderBy('id', 'DESC')->take(5)->get(),
            'top_services_list'                 => Booking::myBooking()->showServiceCount()->take(5)->get(),
            'count_handyman_pending_booking'    => Booking::myBooking()->where('status', 'pending')->count(),
            'count_handyman_complete_booking'   => Booking::myBooking()->where('status', 'completed')->count(),
            'count_handyman_cancelled_booking'  => Booking::myBooking()->where('status', 'cancelled')->count()
        ];

        $data['category_chart'] = [
            'chartdata'     => Booking::myBooking()->showServiceCount()->take(4)->get()->pluck('count_pid'),
            'chartlabel'    => Booking::myBooking()->showServiceCount()->take(4)->get()->pluck('service.category.name')
        ];

        $total_revenue  = Payment::where('payment_status', 'paid');
        if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
            $data['revenueData']    =  adminEarning();
        }

        if ($user->hasRole('provider')) {
            $revenuedata = ProviderPayout::selectRaw('sum(amount) as total , DATE_FORMAT(created_at , "%m") as month')
                ->where('provider_id', auth()->user()->id)
                ->whereYear('created_at', date('Y'))
                ->groupBy('month');
            $revenuedata = $revenuedata->get()->toArray();
            $data['revenueData']    =    [];
            $data['revenuelableData']    =    [];
            for ($i = 1; $i <= 12; $i++) {
                $revenueData = 0;

                foreach ($revenuedata as $revenue) {
                    if ((int)$revenue['month'] == $i) {
                        $data['revenueData'][] = (int)$revenue['total'];
                        $revenueData++;

                    }
                }
                if ($revenueData == 0) {
                    $data['revenueData'][] = 0;
                }
            }

            $data['currency_data']=currency_data();
        }

        $data['total_revenue']  =    $total_revenue->sum('total_amount');

        if ($user->hasRole('provider')) {
            $total_revenue  = ProviderPayout::where('provider_id', $user->id)->sum('amount') ?? 0;
            $data['total_revenue'] = $total_revenue;
        }

        if ($user->hasRole('handyman')) {
            $handyman_total_revenue = HandymanPayout::where('handyman_id', $user->id)->sum('amount') ?? 0;
            $data['total_revenue']  = $handyman_total_revenue;
        }

        $wallet = Wallet::where('user_id', getLoggedUserId())->pluck('amount');

        $data['wallet_balance'] = 0;

        if ($wallet->count() > 0) {
            $data['wallet_balance'] = $wallet[0];
        }

        if (auth()->user()->hasAnyRole(['admin', 'demo_admin'])) {
            return $this->adminDashboard($data);
        } else if (auth()->user()->hasAnyRole('provider')) {
            return $this->providerDashboard($data);
        } else if (auth()->user()->hasAnyRole('handyman')) {
            return $this->handymanDashboard($data);
        } else {
            return $this->userDashboard($data);
        }
    }

    /**
     * Admin Dashboard
     *
     * @param $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminDashboard($data)
    {
        $show = "false";
        $dashboard_setting = Setting::where('type', 'dashboard_setting')->first();

        if ($dashboard_setting == null) {
            $show = "true";
        }
        return view('dashboard.dashboard', compact('data', 'show'));
    }
    public function providerDashboard($data)
    {
        $show = "false";
        $provider_dashboard_setting = Setting::where('type', 'provider_dashboard_setting')->first();

        if ($provider_dashboard_setting == null) {
            $show = "true";
        }
        return view('dashboard.provider-dashboard', compact('data', 'show'));
    }
    public function handymanDashboard($data)
    {
        $show = "false";
        $handyman_dashboard_setting = Setting::where('type', 'handyman_dashboard_setting')->first();

        if ($handyman_dashboard_setting == null) {
            $show = "true";
        }
        return view('dashboard.handyman-dashboard', compact('data', 'show'));
    }
    public function userDashboard($data)
    {
        return view('dashboard.user-dashboard', compact('data'));
    }
    public function changeStatus(Request $request)
    {
//        if (demoUserPermission()) {
//            $message = __('messages.demo_permission_denied');
//            $response = [
//                'status'    => false,
//                'message'   => $message
//            ];
//
//            return comman_custom_response($response);
//        }
        $type = $request->type;
        $data = $request->all();
        $message_form = __('messages.item');
        $message = trans('messages.update_form', ['form' => trans('messages.status')]);
        switch ($type) {
            case 'role':
                $role = \App\Models\Role::find($request->id);
                $role->status = $request->status;
                $role->save();
                break;
            case 'category_status':

                $category = \App\Models\Category::find($request->id);
                $category->status = $request->status;
                $category->save();
                break;
            case 'category_featured':
                $message_form = __('messages.category');
                $category = \App\Models\Category::find($request->id);
                $category->is_featured = $request->status;
                $category->save();
                break;
            case 'service_status':
                $service = \App\Models\Service::find($request->id);
                $service->status = $request->status;
                $service->save();
                break;
            case 'user_service_status':
                $service = \App\Models\Service::with('department.requiredDepartmentCertificates', 'uploadedRequiredCertificates', 'verifiedRequiredCertificates')->find($request->id);

                if (!empty($service) && $service->admin_service_type != 'common' && !empty($service->department) && !empty($service->department->requiredDepartmentCertificates)) {
                    if ($service->certificates->count() == 0) {
                        return comman_custom_response(['message' => __('messages.document_not_uploaded'), 'status' => false]);
                    }

                    if ($service->uploadedRequiredCertificates->count() < $service->department->requiredDepartmentCertificates->count()) {
                        return comman_custom_response(['message' => __('messages.document_not_uploaded'), 'status' => false]);
                    }

                    if ($service->verifiedRequiredCertificates->count() < $service->department->requiredDepartmentCertificates->count()) {
                        return comman_custom_response(['message' => __('messages.document_not_verified'), 'status' => false]);
                    }
                }

                $service->user_service_status = ($request->status==1) ? $request->status : 2;
                $service->save();

                $user = User::getUserByKeyValue('id', $service->provider_id);

                if (!empty($user)) {
                    $type = "service_verification_update";

                    $notification_data = [
                        'id' => $service->id,
                        'type' => $type,
                        'subject' => 'Service Verification Update',
                        'message' => $request->status == 1 ? 'Your service (' . $service->name . ') has been approved.' : 'Sorry, your service (' . $service->name . ') has been rejected by the admin.',
                    ];

                    notificationSend($user, $type, $notification_data);
                }

                break;
            case 'coupon_status':
                $coupon = \App\Models\Coupon::find($request->id);
                $coupon->status = $request->status;
                $coupon->save();
                break;
            case 'document_status':
                $document = \App\Models\Documents::find($request->id);
                $document->status = $request->status;
                $document->save();
                break;
            case 'document_required':
                $message_form = __('messages.document');
                $document = \App\Models\Documents::find($request->id);
                $document->is_required = $request->status;
                $document->save();
                break;
            case 'provider_is_verified':
                $message_form = __('messages.providerdocument');
                $document = \App\Models\ServiceCertificate::find($request->id);

                $document->is_approved = $request->status;
                $document->save();

//                $user = User::getUserByKeyValue('id', $document->provider_id);
//
//                if (!empty($user)) {
//                    $type = "document_verification_update";
//
//                    $notification_data = [
//                        'id' => $document->id,
//                        'type' => $type,
//                        'subject' => 'Document Verification Update',
//                        'message' => $request->status == 1 ? 'Your ID is verified.' : 'Sorry, your document has been rejected by the admin.',
//                    ];
//
//                    notificationSend($user, $type, $notification_data);
//                }
                break;
            case 'tax_status':
                $tax = \App\Models\Tax::find($request->id);
                $tax->status = $request->status;
                $tax->save();
                break;
            case 'provideraddress_status':
                $provideraddress = \App\Models\ProviderAddressMapping::find($request->id);
                $provideraddress->status = $request->status;
                $provideraddress->save();
                break;
            case 'slider_status':
                $slider = \App\Models\Slider::find($request->id);
                $slider->status = $request->status;
                $slider->save();
                break;
            case 'servicefaq_status':
                $servicefaq = \App\Models\ServiceFaq::find($request->id);
                $servicefaq->status = $request->status;
                $servicefaq->save();
                break;
            case 'wallet_status':
                $wallet = \App\Models\Wallet::find($request->id);
                $wallet->status = $request->status;
                $wallet->save();
                break;
            case 'subcategory_status':
                $subcategory = \App\Models\SubCategory::find($request->id);
                $subcategory->status = $request->status;
                $subcategory->save();
                break;
            case 'subcategory_featured':
                $message_form = __('messages.subcategory');
                $subcategory = \App\Models\SubCategory::find($request->id);
                $subcategory->is_featured = $request->status;
                $subcategory->save();
                break;
            case 'plan_status':
                $plans = \App\Models\Plans::find($request->id);
                $plans->status = $request->status;
                $plans->save();
                break;
            case 'bank_status':
                $banks = \App\Models\Bank::find($request->id);
                $banks->status = $request->status;
                $banks->save();
                break;
            case 'blog_status':
                $blog = \App\Models\Blog::find($request->id);
                $blog->status = $request->status;
                $blog->save();
                break;
            case 'servicepackage_status':
                $servicepackage = \App\Models\ServicePackage::find($request->id);
                $servicepackage->status = $request->status;
                $servicepackage->save();
                break;
            case 'serviceaddon_status':
                $serviceaddon = \App\Models\ServiceAddon::find($request->id);
                $serviceaddon->status = $request->status;
                $serviceaddon->save();
                break;
            case 'wallet_withdraw':
                $walletWithdraw = WalletWithdraw::with(['user.wallet'])->where('id', $data['id'])->first();

                DB::beginTransaction();

                try {
                    $walletWithdraw->reject_reason = !empty($data['reason']) ? $data['reason'] : null;
                    $walletWithdraw->status = $data['status'];

                    $message = __('messages.wallet_withdraw_reject');

                    $message = "Admin has reject your withdraw request due to: " . $walletWithdraw->reject_reason;

                    if ($data['status'] === 'paid') {
                        if ($walletWithdraw->user->wallet->amount < $walletWithdraw->amount) {
                            return comman_custom_response(['message' => __('messages.provider_wallet_balance_error'), 'status' => false]);
                        }

                        $providerWallet = Wallet::where('id', $walletWithdraw->user->wallet->id)->first();

                        $providerWallet->amount -= $walletWithdraw->amount;
                        $providerWallet->save();
                        $message = "Your request of the withdraw from the wallet has been mark as paid by the admin";

                        $message = __('messages.wallet_withdraw_paid');
                    }

                    $walletWithdraw->save();

                    $notification_data = [
                        'id' => $walletWithdraw->id,
                        'type' => "wallet_withdraw_request_update",
                        'subject' => "Wallet Withdraw Request Update",
                        'message' => $message,
                    ];
                    notificationSend($walletWithdraw->user, $type, $notification_data);
                    DB::commit();

                    return comman_custom_response(['message' => $message, 'status' => true], 200);
                } catch (\Exception $e) {
                    DB::rollBack();
                    return comman_custom_response(['message' => $e->getMessage(), 'status' => false], 500);
                }
                break;
            default:
                $message = 'error';
                break;
        }
        if ($request->has('is_featured') && $request->is_featured == 'is_featured') {
            $message =  __('messages.added_form', ['form' => $message_form]);
            if ($request->status == 0) {
                $message = __('messages.remove_form', ['form' => $message_form]);
            }
        }
        if ($request->has('is_required') && $request->is_required == 'is_required') {
            $message =  __('messages.added_form', ['form' => $message_form]);
            if ($request->status == 0) {
                $message = __('messages.remove_form', ['form' => $message_form]);
            }
        }
        if ($request->has('provider_is_verified') && $request->provider_is_verified == 'provider_is_verified') {
            $message =  __('messages.added_form', ['form' => $message_form]);
            if ($request->status == 0) {
                $message = __('messages.remove_form', ['form' => $message_form]);
            }
        }
        return comman_custom_response(['message' => $message, 'status' => true]);
    }

    public function getAjaxList(Request $request)
    {
        $items = array();
        $value = $request->q;

        $auth_user = authSession();
        switch ($request->type) {
            case 'permission':
                $items = \App\Models\Permission::select('id', 'name as text')->whereNull('parent_id');
                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;

            case 'department':
                $items = \App\Models\Department::select('id', 'name as text');
                if(auth()->user()->user_type == 'provider'){
                    $items->where('id', auth()->user()->department_id);
                }
                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;
            case 'departmentbyid':
                $items = \App\Models\Department::select('*')->withCount('departmentShiftHoursMapping')->where('id', $request->department);

                $items = $items->get();
                break;
            case 'pricetype':
                $query = \App\Models\Department::select('*')->with('departmentPriceTypesMapping.priceTypes')->where('id', $request->department);
                $queryData = $query->first();
                $data = [];
                foreach ($queryData->departmentPriceTypesMapping as $mapping) {
                    if ($mapping->priceTypes) {
                        $data['id'] = $mapping->priceTypes->id;
                        $data['text'] = $mapping->priceTypes->name;
                    }
                    $items[] = $data;
                }
            break;
            case 'servicetypebydept':
                if($request->subcategory && $request->subcategory == 74){
                    $query = \App\Models\SubCategory::select('*')->where('id', $request->subcategory);
                }
                else{
                $query = \App\Models\DepartmentTypesMapping::select('types.id', 'types.name')
                ->join('types', 'types.id' , '=', 'department_types.type_id')
                ->where('department_id', $request->department)
                ->get()->toArray();
                    // $query = \App\Models\Service::select('*')->with('departmentType')->where('id', $request->department);
                }

                $data = [];
                foreach ($query as $mapping) {
                    $data['id'] = $mapping['id'];
                    $data['text'] = $mapping['name'];
                    $items[] = $data;
                }
            break;
            case 'servicematerialbydept':

                $query = \App\Models\DepartmentMaterialUnitsMapping::select('material_units.id', 'material_units.name')
                ->join('material_units', 'material_units.id' , '=', 'department_material_units.material_unit_id')
                ->where('department_id', $request->department)
                ->get()->toArray();

                $data = [];
                foreach ($query as $mapping) {
                    $data['id'] = $mapping['id'];
                    $data['text'] = $mapping['name'];
                    $items[] = $data;
                }
            break;
            case 'shifttype':
                $query = \App\Models\DepartmentShiftHoursMapping::select('shift_type_id', 'shift_types.name')
                ->join('shift_hours', 'shift_hours.id' , '=', 'department_shift_hours.shift_hours_id')
                ->join('shift_types', 'shift_types.id' , '=', 'shift_hours.shift_type_id')

                ->where('department_id', $request->department)
                ->groupBy('shift_hours.shift_type_id')
                ->get()->toArray();


                $data = [];
                foreach ($query as $mapping) {
                    $data['id'] = $mapping['shift_type_id'];
                    $data['text'] = $mapping['name'];
                    $items[] = $data;
                }
            break;
            case 'shifttypehours':
                $query = \App\Models\ShiftHour::select('shift_type_id', DB::raw("CONCAT(hours_from, ' - ', hours_to) as text"))
                ->join('shift_types', 'shift_types.id' , '=', 'shift_hours.shift_type_id')
                    ->where(['shift_hours.shift_type_id' => $request->shift_type_id])
                    ->get()->toArray();

                $data = [];
                foreach ($query as $mapping) {
                    $data['id'] = $mapping['shift_type_id'];
                    $data['text'] = $mapping['text'];
                    $items[] = $data;
                }
            break;
            case 'category':
                $items = \App\Models\Category::select('id', 'name as text')->where('status', 1);

                if (!empty($request->department)) {
                    $items->where('department_id', $request->department);
                }

                if ($value != '') {
                    $items->where('name', 'LIKE', '%' . $value . '%');
                }

                if ($auth_user && $auth_user->user_type=='provider') {
                    $provider_data = User::find($auth_user->id);

                    $categories = $provider_data->providerCategoryMapping->pluck('category_id');
                    if ($categories != null) {
                        $items->whereIn('id', $categories);
                    }
                }

                $items = $items->get();
                if(count($items)>0 && !isset($request->is_all_option))
                {
                    $items = array_merge([['id' => 0, 'text' => 'All']], $items->toArray());
                }

                break;
            case 'subcategory':
                $items = \App\Models\SubCategory::select('id', 'name as text')->where('status', 1);

                if ($value != '') {
                    $items->where('name', 'LIKE', '%' . $value . '%');
                }

                $items = $items->get();
                break;
            case 'provider':
                $items = \App\Models\User::select('id', 'display_name as text')
                    ->where('user_type', 'provider')
                    ->where('is_subscribe', 1)
                    ->where('status', 1);

                if (isset($request->booking_id)) {
                    $booking_data = Booking::find($request->booking_id);

                    $providers = $booking_data->providerAdded->pluck('provider_id');
                    if ($providers != null) {
                        $items->whereIn('id', $providers);
                    }
                }

                if (!empty($request->category_id)) {
                    if (empty($request->subcategory_id) || $request->subcategory_id == 'null') {
                        $providerIds = ProviderCategoryMapping::where('category_id', $request->category_id)->pluck('provider_id');
                        $items->whereIn('id', $providerIds);
                    } else {
                        $providerIds = ProviderCategoryMapping::where('sub_category_id', $request->subcategory_id)->pluck('provider_id');
                        $items->whereIn('id', $providerIds);
                    }
                }

                if ($value != '') {
                    $items->where('display_name', 'LIKE', $value . '%');
                }

                $items = $items->get();
                break;

            case 'user':
                $items = \App\Models\User::select('id', 'display_name as text')
                    ->where('user_type', 'user')
                    ->where('status', 1);

                if ($value != '') {
                    $items->where('display_name', 'LIKE', $value . '%');
                }

                $items = $items->get();
                break;

                case 'provider-user':
                    $items = \App\Models\User::select('id', 'display_name as text')
                        ->where('user_type', 'provider')->orWhere('user_type','user')
                        ->where('status', 1);

                    if ($value != '') {
                        $items->where('display_name', 'LIKE', $value . '%');
                    }

                    $items = $items->get();
                    break;

            case 'handyman':
                $items = \App\Models\User::select('id', 'display_name as text')
                    ->where('user_type', 'handyman')
                    ->where('status', 1);

                if (isset($request->provider_id)) {
                    $items->where('provider_id', $request->provider_id);
                }

                if (isset($request->booking_id)) {
                    $booking_data = Booking::find($request->booking_id);

                    $service_address = $booking_data->handymanByAddress;
                    if ($service_address != null) {
                        $items->where('service_address_id', $service_address->id);
                    }
                }

                if ($value != '') {
                    $items->where('display_name', 'LIKE', $value . '%');
                }

                $items = $items->get();
                break;
            case 'service':
                $items = \App\Models\Service::select('id', 'name as text')->where('end_date', '>=', Carbon::now()->toDateString())->where('status', 1);

                if (!$request->has('without_approval')) {
                    $items = $items->where('user_service_status', 1);
                }

                if ($value != '') {
                    $items->where('name', 'LIKE', '%' . $value . '%');
                }
                if (isset($request->provider_id)) {
                    $items->where('provider_id', $request->provider_id);
                } else {
                    if (getLoggedUserType() === 'provider') {
                        $items->where('provider_id', getLoggedUserId());
                    }
                }

                $items = $items->get();
                break;
            case 'service-list':
                    $items = \App\Models\Service::select('id', 'name as text')->where('status', 1)->where('service_type','service');

                    if ($value != '') {
                        $items->where('name', 'LIKE', '%' . $value . '%');
                    }
                    if (isset($request->provider_id)) {
                        $items->where('provider_id', $request->provider_id);
                    }

                    $items = $items->get();
                    break;
            case 'providertype':
                $items = \App\Models\ProviderType::select('id', 'name as text')
                    ->where('status', 1);

                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }

                $items = $items->get();
                break;
            case 'coupon':
                $items = \App\Models\Coupon::select('id', 'code as text')->where('status', 1);

                if ($value != '') {
                    $items->where('code', 'LIKE', '%' . $value . '%');
                }

                $items = $items->get();
                break;

                case 'bank':
                    $items = \App\Models\Bank::select('id', 'bank_name as text')->where('provider_id',$request->provider_id)->where('status',1);

                    if ($value != '') {
                        $items->where('name', 'LIKE', $value . '%');
                    }
                    $items = $items->get();
                    break;

            case 'country':
                $items = \App\Models\Country::select('id', 'name as text');

                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;
            case 'state':
                $items = \App\Models\State::select('id', 'name as text');
                if (isset($request->country_id)) {
                    $items->where('country_id', $request->country_id);
                }
                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;
            case 'city':
                $items = \App\Models\City::select('id', 'name as text');
                if (isset($request->state_id)) {
                    $items->where('state_id', $request->state_id);
                }
                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;
            case 'booking_status':
                $items = \App\Models\BookingStatus::select('id', 'label as text');

                if ($value != '') {
                    $items->where('label', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;
            case 'currency':
                $items = \DB::table('countries')->select(\DB::raw('id id,CONCAT(name , " ( " , symbol ," ) ") text'));

                $items->whereNotNull('symbol')->where('symbol', '!=', '');
                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%')->orWhere('currency_code', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;
            case 'country_code':
                $items = \DB::table('countries')->select(\DB::raw('code id,name text'));
                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%')->orWhere('code', 'LIKE', $value . '%');
                }
                $items = $items->get();
                break;

            case 'time_zone':
                $items = timeZoneList();

                foreach ($items as $k => $v) {

                    if ($value != '') {
                        if (strpos($v, $value) !== false) {
                        } else {
                            unset($items[$k]);
                        }
                    }
                }

                $data = [];
                $i = 0;
                foreach ($items as $key => $row) {
                    $data[$i] = [
                        'id'    => $key,
                        'text'  => $row,
                    ];
                    $i++;
                }
                $items = $data;
                break;
            case 'provider_address':
                $provider_id = !empty($request->provider_id) ? explode(',', $request->provider_id) : [$auth_user->id];

                $items = \App\Models\ProviderAddressMapping::select('id', 'address as text', 'latitude', 'longitude', 'status', 'provider_id')
                        ->where(function ($query) use ($provider_id) {
                            foreach ($provider_id as $index => $id) {
                                if ($index === 0) {
                                    $query = $query->where('provider_id', $id);
                                } else {
                                    $query = $query->orWhere('provider_id', $id);
                                }
                            }
                            return $query;
                    })
                    ->get();
                break;

            case 'provider_tax':
                $provider_id = !empty($request->provider_id) ? $request->provider_id : $auth_user->id;
                $items = \App\Models\Tax::select('id', 'title as text')->where('status', 1);
                $items = $items->get();
                break;

            case 'documents':
                $serviceId = $request->service_id;
                $service = Service::with('department.requiredDepartmentCertificates', 'department.optionalDepartmentCertificates')->where('id', $serviceId)->first();

                $serviceDocuments = [];

                if (!empty($service) && !empty($service->department)) {
                    if (!empty($service->department->requiredDepartmentCertificates)) {
                        foreach ($service->department->requiredDepartmentCertificates as $certificate) {
                            $serviceDocuments[] = [
                                'id' => $certificate->id,
                                'text' => $certificate->name . " *"
                            ];
                        }
                    }
                    if (!empty($service->department->optionalDepartmentCertificates)) {
                        foreach ($service->department->optionalDepartmentCertificates as $certificate) {
                            $serviceDocuments[] = [
                                'id' => $certificate->id,
                                'text' => $certificate->name
                            ];
                        }
                    }
                }

                return $serviceDocuments;
            case 'handymantype':
                $items = \App\Models\HandymanType::select('id', 'name as text')
                    ->where('status', 1);

                if ($value != '') {
                    $items->where('name', 'LIKE', $value . '%');
                }

                $items = $items->get();
                break;
            case 'subcategory_list':
                $items = \App\Models\SubCategory::select('id', 'name as text')->where('status', 1);

                if(!empty($request->category_id))
                {
                    $items->whereIn('category_id', explode(',',$request->category_id));
                }
                elseif($request->category_id=='')
                {
                    $items->take(0);
                }

                if ($auth_user && $auth_user->user_type=='provider') {
                    $providerData = User::find($auth_user->id);

                    $subCategories = $providerData->providerCategoryMapping->pluck('sub_category_id');
                    if ($subCategories != null) {
                        $items->whereIn('id', $subCategories);
                    }
                }

                $items = $items->get();

                if(count($items)>0 && !isset($request->is_all_option)){
                    $items = array_merge([['id' => 0, 'text' => 'All']], $items->toArray());
                }

                break;
            case 'service_package':
                $service_id = !empty($request->service_id) ? $request->service_id : $auth_user->id;
                $items = \App\Models\ServicePackage::select('id', 'description as text', 'status')->where('provider_id', $service_id)->where('status', 1);
                $items = $items->get();
                break;
            case 'all_user':
                $items = \App\Models\User::select('id', 'display_name as text')
                    ->where('status', 1);

                if ($value != '') {
                    $items->where('display_name', 'LIKE', $value . '%');
                }

                $items = $items->get();
                break;
            case 'shift_hours_list':
                $items = \App\Models\ShiftHour::select('id', DB::raw("CONCAT(hours_from, ' - ', hours_to) as text"));

                if(!empty($request->shift_type_id))
                {
                    $items->whereIn('shift_type_id', explode(',',$request->shift_type_id));
                }
                elseif($request->shift_type_id=='')
                {
                    $items->take(0);
                }

                $items = $items->get();

                if (count($items) > 0) {
                    $items = array_merge([['id' => 0, 'text' => 'All']], $items->toArray());
                }

                break;
            default:
                break;
        }
        return response()->json(['status' => 'true', 'results' => $items]);
    }

    public function removeFile(Request $request)
    {
        if (demoUserPermission()) {
            $message = __('messages.demo_permission_denied');
            $response = [
                'status'    => false,
                'message'   => $message
            ];

            return comman_custom_response($response);
        }

        $type = $request->type;
        $data = null;
        switch ($type) {
            case 'slider_image':
                $data = Slider::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.slider')]);
                break;
            case 'profile_image':
                $data = User::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.profile_image')]);
                break;
            case 'service_attachment':
                $media = Media::find($request->id);
                $media->delete();
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'category_image':
                $data = Category::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.category')]);
                break;
            case 'provider_document':
                $data = ProviderDocument::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.providerdocument')]);
                break;
            case 'booking_attachment':
                $media = Media::find($request->id);
                $media->delete();
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'bank_attachment':
                $media = Media::find($request->id);
                $media->delete();
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'app_image':
                $data = AppDownload::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'app_image_full':
                $data = AppDownload::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'package_attachment':
                $media = Media::find($request->id);
                $media->delete();
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'blog_attachment':
                $media = Media::find($request->id);
                $media->delete();
                $message = __('messages.msg_removed', ['name' => __('messages.attachments')]);
                break;
            case 'serviceaddon_image':
                $data = ServiceAddon::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.service_addon')]);
                break;
            default:
                $data = AppSetting::find($request->id);
                $message = __('messages.msg_removed', ['name' => __('messages.image')]);
                break;
        }

        if ($data != null) {
            $data->clearMediaCollection($type);
        }

        $response = [
            'status'    => true,
            'image'     => getSingleMedia($data, $type),
            'id'        => $request->id,
            'preview'   => $type . "_preview",
            'message'   => $message
        ];

        return comman_custom_response($response);
    }

    public function lang($locale)
    {
        \App::setLocale($locale);
        session()->put('locale', $locale);
        \Artisan::call('cache:clear');
        $dir = 'ltr';
        if (in_array($locale, ['ar', 'dv', 'ff', 'ur', 'he', 'ku', 'fa'])) {
            $dir = 'rtl';
        }

        session()->put('dir',  $dir);
        return redirect()->back();
    }

    function authLogin()
    {
        return view('auth.new-login');
    }
    function authRegister()
    {
        return view('auth.register');
    }

    function authRecoverPassword()
    {
        return view('auth.forgot-password');
    }

    function authConfirmEmail()
    {
        return view('auth.verify-email');
    }
    function getAjaxServiceList(Request $request){
        $items = \App\Models\Service::select('id', 'name as text')->where('end_date', '>=', Carbon::now()->toDateString())->where('user_service_status', 1)->where('status', 1)->where('type', 'fixed');

        $provider_id = !empty($request->provider_id) ? $request->provider_id : auth()->user()->id;
        $items->where('provider_id', $provider_id );
        if (isset($request->category_id)) {
            $items->where('category_id', $request->category_id);
        }
        if (isset($request->subcategory_id)) {
            $items->where('subcategory_id', $request->subcategory_id);
        }
        $items = $items->get();
        return response()->json(['status' => 'true', 'results' => $items]);
    }

    public function chat(Request $request)
    {
        $user = auth()->user();
        $user->chat_last_notification_seen = now();
        $user->save();
        return view('chat');
    }
}
