<?php

namespace App\Http\Controllers;

use App\DataTables\ServiceDataTable;
use App\Http\Requests\UserRequest;
use App\Models\BadgeProvider;
use App\Models\Badges;
use App\Models\Booking;
use App\Models\Category;
use App\Models\PaymentGateway;
use App\Models\ProviderPayout;
use App\Models\ProviderSlotMapping;
use App\Models\ProviderSubscription;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title',['form' => __('messages.provider')] );
        if($request->status === 'pending'){
            $pageTitle = __('messages.pending_list_form_title',['form' => __('messages.provider')] );
        }
        if($request->status === 'subscribe'){
            $pageTitle = __('messages.list_form_title',['form' => __('messages.subscribe')] );
        }

        $auth_user = authSession();
        $assets = ['datatable'];
        $list_status = $request->status;
        return view('provider.index', compact('list_status','pageTitle','auth_user','assets','filter'));
    }

    public function index_data(DataTables $datatable,Request $request)
    {
        $query = User::query();
        $filter = $request->filter;
        $status = $request->list_status;
        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        $query = $query->where('user_type','provider');
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->withTrashed();
        }

        if (!empty($request->list_status)) {
            switch ($request->list_status) {
                case 'pending':
                    $query->where('status', 0)->orderBy('id', 'desc');
                    break;
                case 'subscribe':
                    $query->where('status', 1)->where('is_subscribe', 1)->whereHas('subscriptionPackage')->orderBy('id', 'desc');
                    break;
                default:
                    $query->where('status', 1);
                    break;
            }
        } else {
            $query->where('status', '!=', 0);
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-'.$row->id.'"  name="datatable_ids[]" value="'.$row->id.'" data-type="user" onclick="dataTableRowCheck('.$row->id.',this)">';
            })
            ->editColumn('id', function ($query) {
                return '# ' . $query->id;
            })
            ->editColumn('display_name', function ($query) {
                return view('provider.user', compact('query'));
            })
            ->editColumn('login_type', function ($query) {
                if (empty($query->login_type)) {
                    return "-";
                }
                return Str::title($query->login_type);
            })
            ->editColumn('category', function ($query) {
                if ($query->providerCategoryMapping->count() == 0) {
                    return "-";
                }
                $categoryNames = [];
                foreach ($query->providerCategoryMapping as $categoryMapping) {
                    $categoryNames[] = $categoryMapping->category->name;
                }
                return implode(", ", array_unique($categoryNames));
            })
            ->editColumn('area', function ($query) {
                return !empty($query->area) ? $query->area : '-';
            })
            ->editColumn('sub_category', function ($query) {
                if ($query->providerCategoryMapping->count() == 0) {
                    return "-";
                }

                $subCategoryNames = [];
                foreach ($query->providerCategoryMapping as $subCategoryMapping) {
                    if (!empty($subCategoryMapping->subCategory)) {
                        $subCategoryNames[] = $subCategoryMapping->subCategory->name;
                    }
                }
                return implode(", ", array_unique($subCategoryNames));
            })
            ->editColumn('department_name', function ($query) {
                return !empty($query->department) ? $query->department->name : "-";
            })->editColumn('status', function ($query) {
                if($query->status == '0'){
                    $status = '<a class="btn-sm text-white btn-success"  href='.route('provider.approve',$query->id).'><i class="fa fa-check"></i>Approve</a>';
                }else{
                    $status = '<span class="badge badge-active">'.__('messages.active').'</span>';
                }
                return $status;
            })
            ->editColumn('providertype_id', function($query) {
                return ($query->providertype_id != null && isset($query->providertype)) ? $query->providertype->name : '-';
            })
            ->editColumn('address', function($query) {
                return ($query->address != null && isset($query->address)) ? $query->address : '-';
            })
            ->editColumn('created_at', function($query) {
                $carbonDate = Carbon::parse($query->created_at);
                $formattedDate = $carbonDate->toDateString();

                return $formattedDate;
            })

            ->filterColumn('providertype_id',function($query,$keyword){
                $query->whereHas('providertype',function ($q) use($keyword){
                    $q->where('name','like','%'.$keyword.'%');
                });
            })
            ->addColumn('action', function ($provider) use ($status) {
                return view('provider.action', compact('provider', 'status'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['check','display_name','action','status'])
            ->toJson();
    }

    /* bulck action method */
    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';

        switch ($actionType) {
            case 'change-status':
                $branches = User::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Provider Status Updated';
                break;

            case 'delete':
                User::whereIn('id', $ids)->delete();
                $message = 'Bulk Provider Deleted';
                break;

            case 'restore':
                User::whereIn('id', $ids)->restore();
                $message = 'Bulk Provider Restored';
                break;

            case 'permanently-delete':
                User::whereIn('id', $ids)->forceDelete();
                $message = 'Bulk Provider Permanently Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => 'Bulk Action Updated']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $providerdata = User::with(['providerCategoryMapping'])->find($id);
        $categorydata = Category::where('status',1)->get();
        $subcategorydata = SubCategory::where('status', 1)->get();
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.provider')]);


        $badge_provider_id = 1;
        $badge_provider = BadgeProvider::select('badge_id')->where(['provider_id'   => $id])->first();
        if (!empty($badge_provider)) {
            $badge_provider_id = $badge_provider->badge_id;
        }
        if($providerdata == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.provider')]);
            $providerdata = new User;
        }

        $badgedata = Badges::select('id', 'name', 'badge_color')->get();

        return view('provider.create', compact('pageTitle' ,'providerdata' ,'auth_user', 'categorydata', 'subcategorydata', 'badgedata', 'badge_provider_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        try {
            $loginuser = \Auth::user();
            if(demoUserPermission()){
                return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
            }
            $data = $request->all();

            $id = $data['id'];
            $data['user_type'] = $data['user_type'] ?? 'provider';
            $data['is_featured'] = 0;

            if($request->has('is_featured')){
                $data['is_featured'] = 1;
            }

            $data['display_name'] = $data['first_name'];

            if($id == null){
                $data['password'] = bcrypt($data['password']);
                $data['login_type'] = 'normal';
                $user = User::create($data);
            }else{
                $user = User::findOrFail($id);

                $user->fill($data)->update();
            }
            if(!empty($data['badge_provider_id'])){
                $provider_badge = [
                    'provider_id'   => $user->id,
                    'badge_id'   => $data['badge_provider_id'],
                ];
                if(!$user->providerBadgeMapping()->where($provider_badge)->exists()){
                    $provider_badge_exist = [
                        'provider_id' => $user->id,
                    ];
                    if ($user->providerBadgeMapping()->where($provider_badge_exist)->get()) {
                        $user->providerBadgeMapping()->delete($provider_badge_exist);
                        $databadge = $user->providerBadgeMapping()->insert($provider_badge);
                    }
                }
                else{
                    $databadge = 'exist';
                }

            }
            if($data['status'] == 1 && auth()->user()->hasAnyRole(['admin'])){
                try {
                    \Mail::send('verification.verification_email',
                        array(), function($message) use ($user)
                        {
                            $message->from(env('MAIL_FROM_ADDRESS'));
                            $message->to($user->email);
                        });
                } catch (\Throwable $th) {

                }

            }
            $user->assignRole($data['user_type']);
            storeMediaFile($user,$request->profile_image, 'profile_image');
            $message = __('messages.update_form',[ 'form' => __('messages.provider') ] );
            if($user->wasRecentlyCreated){
                $message = __('messages.save_form',[ 'form' => __('messages.provider') ] );
            }
            if($user->providerTaxMapping()->count() > 0)
            {
                $user->providerTaxMapping()->delete();
            }
            if($request->tax_id != null) {
                foreach($request->tax_id as $tax) {
                    $provider_tax = [
                        'provider_id'   => $user->id,
                        'tax_id'   => $tax,
                    ];
                    $user->providerTaxMapping()->insert($provider_tax);
                }
            }

            if ($user->providerCategoryMapping()->count() > 0) {
                $user->providerCategoryMapping()->delete();
            }

            if (!$request->is('api/*') && count($request->category_id) > 0) {

                $is_category_all = 0;
                $is_sub_category_all = 0;
                if (in_array("0", $request->category_id, TRUE)) {
                    $categories  = Category::where('status', 1)->pluck('id')->toArray();
                    $is_category_all = 1;
                } else {
                    $categories = $request->category_id;
                }

                $subCategories = [];
                if (!isset($request->subcategory_id) || in_array("0", $request->subcategory_id, TRUE)) {
                    $subCategories = SubCategory::where('status', 1)->whereIn('category_id', $categories)->select('id', 'category_id')->get();
                    $is_sub_category_all = 1;
                } elseif (isset($request->subcategory_id) && $request->subcategory_id != null) {
                    $subCategories = SubCategory::where('status', 1)->whereIn('id', $request->subcategory_id)->select('id', 'category_id')->get();
                }

                if (count($subCategories) > 0) {
                    foreach ($subCategories as $subCategory) {

                        if (($key = array_search($subCategory->category_id, $categories)) !== false) {
                            unset($categories[$key]);
                        }

                        $provider_category = [
                            'provider_id' => $user->id,
                            'category_id' => $subCategory->category_id,
                            'sub_category_id' => $subCategory->id,
                            'is_category_all' => $is_category_all,
                            'is_sub_category_all' => $is_sub_category_all
                        ];
                        $user->providerCategoryMapping()->create($provider_category);
                    }
                }
            } elseif ($request->is('api/*')) {
                $provider_category = [
                    'provider_id' => $user->id,
                    'category_id' => $request->category_id,
                    'sub_category_id' => $request->sub_category_id,
                ];
                $user->providerCategoryMapping()->create($provider_category);
            }

            if($request->is('api/*')) {
                return comman_message_response($message);
            }

            switch ($data['filter_status']) {
                case 'pending':
                    return redirect(route('provider.pending', 'pending'))->withSuccess($message);

                case 'subscribe':
                    return redirect(route('provider.pending', 'subscribe'))->withSuccess($message);

                default:
                    return redirect(route('provider.index'))->withSuccess($message);
            }

        } catch (\Exception $e) {
            echo $e->getMessage() . " " . $e->getFile() . " " . $e->getLine();
            die;
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceDataTable $dataTable,$id)
    {
        $auth_user = authSession();
        $providerdata = User::with('providerDocument', 'booking')->where('user_type', 'provider')->where('id', $id)->first();

        $data =  Booking::where('provider_id', $id)->selectRaw(
            'COUNT(CASE WHEN status = "pending" THEN "pending" END) AS pendingStatusCount,
                                    COUNT(CASE WHEN status = "Cancelled"  THEN "Cancelled" END) AS cancelledstatuscount,
                                    COUNT(CASE WHEN status = "Completed"  THEN "Completed" END) AS Completedstatuscount,
                                    COUNT(CASE WHEN status = "Accepted"  THEN "Accepted" END) AS Acceptedstatuscount,
                                    COUNT(CASE WHEN status = "Ongoing"  THEN "Ongoing" END) AS Ongoingstatuscount'
        )->first()->toArray();


        $providerTotEarning = User::withSum('providerBooking', 'total_amount')->find($id);

        $providerPayout  = ProviderPayout::where('provider_id',$id)->sum('amount');

        $providerData = [
            'providerTotEarning' => $providerTotEarning->provider_booking_sum_total_amount,
            'providerTotWithdrableAmt' => $providerTotEarning->provider_booking_sum_total_amount,
            'providerAlreadyWithdrawAmt' => $providerPayout,
            'pendWithdrwan' => $providerTotEarning->provider_booking_sum_total_amount - $providerPayout,
        ];

        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);
        return $dataTable
            ->with('provider_id', $id)
            ->render('provider.view', compact('pageTitle', 'providerdata', 'auth_user', 'data','providerTotEarning','providerPayout','providerData'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $provider = User::find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.provider')] );

        if($provider != '') {
            $provider->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.provider')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }
    public function action(Request $request){
        $id = $request->id;

        $provider  = User::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.provider')] );
        if($request->type == 'restore') {
            $provider->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.provider')] );
        }

        if($request->type === 'forcedelete'){
            $provider->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.provider')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
    public function bankDetails(ServiceDataTable $dataTable, Request $request)
    {
        $auth_user = authSession();
        $providerdata = User::with('getServiceRating')->where('user_type', 'provider')->where($request->id)->first();
        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);
        return $dataTable
            ->with('provider_id', $request->id)
            ->render('provider.bank-details', compact('pageTitle', 'providerdata', 'auth_user'));
    }

    public function review(Request $request, $id)
    {
        $auth_user = authSession();
        $providerdata = User::with('getServiceRating')->where('user_type', 'provider')->where('id', $id)->first();
        $earningData = array();
        foreach ($providerdata->getServiceRating as $bookingreview) {

            $booking_id = $bookingreview->id;
            $date = optional($bookingreview->booking)->date ?? '-';
            $rating = $bookingreview->rating;
            $review = $bookingreview->review;
            $earningData[] = [
                'booking_id'=>$booking_id,
                'date' => $date,
                'rating' => $rating,
                'review' => $review,
            ];
        }
        if ($request->ajax()) {
            return Datatables::of($earningData)
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        }

        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);
        return view('provider.review', compact('pageTitle','earningData', 'auth_user', 'providerdata'));
    }
    public function providerDetail(Request $request)
    {

        $tabpage = $request->tabpage;
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.service')]);
        $auth_user = authSession();
        $user_id = $auth_user->id;
        $user_data = User::find($user_id);
        $earningData = array();
        $payment_data = PaymentGateway::where('type', $tabpage)->first();
        $provideId = $request->providerId;
        $plandata = ProviderSubscription::where('user_id',$request->providerid)->get();
        if($request->tabpage == 'subscribe-plan'){
            $plandata = $plandata->where('plan_type','subscribe');
        }if($request->tabpage == 'unsubscribe-plan'){
            $plandata = $plandata->where('plan_type','unsubscribe');
        }
        switch ($tabpage) {
            case 'all-plan':

                if ($request->ajax() && $request->type == 'tbl') {
                 return  Datatables::of($plandata)
                   ->addIndexColumn()
                   ->rawColumns([])
                   ->make(true);
                }

               return view('providerdetail.all-plan', compact('user_data', 'earningData', 'tabpage', 'auth_user', 'payment_data','provideId'));
                break;
            case 'subscribe-plan':
                if ($request->ajax() && $request->type == 'tbl') {
                    return  Datatables::of($plandata)
                      ->addIndexColumn()
                      ->rawColumns([])
                      ->make(true);
                   }
                   return view('providerdetail.subscribe-plan', compact('user_data', 'earningData', 'tabpage', 'auth_user', 'payment_data','provideId'));

                break;
            case 'unsubscribe-plan':
                if ($request->ajax() && $request->type == 'tbl') {
                    return  Datatables::of($plandata)
                      ->addIndexColumn()
                      ->rawColumns([])
                      ->make(true);
                   }
                   return view('providerdetail.unsubscribe-plan', compact('user_data', 'earningData', 'tabpage', 'auth_user', 'payment_data','provideId'));

                break;
            default:
                $data  = view('providerdetail.' . $tabpage, compact('tabpage', 'auth_user', 'payment_data'))->render();
                break;
        }

       return response()->json($data);
    }

    public function approve($id){
        $provider = User::find($id);
        $provider->status = 1;
        $provider->save();
        $msg = __('messages.approve_successfully');
        return redirect()->back()->withSuccess($msg);
    }

    public function getChangePassword(Request $request){
        $id = $request->id;
        $auth_user = authSession();

        $providerdata = User::find($id);
        $pageTitle = __('messages.change_password',['form'=> __('messages.change_password')]);
        return view('provider.changepassword', compact('pageTitle' ,'providerdata' ,'auth_user'));
    }

    public function changePassword(Request $request)
    {
        if (demoUserPermission()) {
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $user = User::where('id', $request->id)->first();

        if ($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message, 400);
        }

        $validator = \Validator::make($request->all(), [
            'old' => 'required|min:6|max:255',
            'password' => 'required|min:6|confirmed|max:255',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('password')) {
                $message = __('messages.confirmed',['name' => __('messages.password')]);
                return redirect()->route('provider.changepassword', ['id' => $user->id])->with('error', $message);
            }
            return redirect()->route('provider.changepassword', ['id' => $user->id])->with('errors', $validator->errors());
        }

        $hashedPassword = $user->password;

        $match = Hash::check($request->old, $hashedPassword);

        $same_exits = Hash::check($request->password, $hashedPassword);
        if ($match) {
            if ($same_exits) {
                $message = __('messages.old_new_pass_same');
                return redirect()->route('provider.changepassword',['id' => $user->id])->with('error', $message);
            }

            $user->fill([
                'password' => Hash::make($request->password)
            ])->save();
            $message = __('messages.password_change');
            return redirect()->route('provider.index')->withSuccess($message);
        } else {
            $message = __('messages.valid_password');
            return redirect()->route('provider.changepassword',['id' => $user->id])->with('error', $message);
        }
    }
    public function getProviderTimeSlot(Request $request){
        $id = $request->id;
        $provider = User::find($id);
        date_default_timezone_set($admin->time_zone ?? 'UTC');

        $current_time = \Carbon\Carbon::now();
        $time = $current_time->toTimeString();

        $current_day = strtolower(date('D'));

        $provider_id = $request->id ?? auth()->user()->id;

        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        $slotsArray = ['days' => $days];
        $activeDay ='mon';
        foreach ($days as $value) {
            $slot = ProviderSlotMapping::where('provider_id', $provider_id)
            ->where('days', $value)
            ->orderBy('start_at', 'asc')
            ->pluck('start_at')
            ->toArray();

            $obj = [
                "day" => $value,
                "slot" => $slot,
            ];
            $slotsArray[] = $obj;
        }

        $pageTitle = __('messages.slot', ['form' => __('messages.slot')]);
        return view('provider.timeslot', compact('slotsArray', 'pageTitle', 'activeDay','provider_id','provider'));
    }

    public function editProviderTimeSlot(Request $request){
        $id = $request->id;
        $provider = User::find($id);
        date_default_timezone_set($admin->time_zone ?? 'UTC');

        $current_time = \Carbon\Carbon::now();
        $time = $current_time->toTimeString();

        $current_day = strtolower(date('D'));

        $provider_id = $request->id ?? auth()->user()->id;

        $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];

        $slotsArray = ['days' => $days];
        $activeDay = 'mon';
        $activeSlots = [];

        foreach ($days as $value) {
            $slot = ProviderSlotMapping::where('provider_id', $provider_id)
            ->where('days', $value)
            ->orderBy('start_at', 'asc')
            ->selectRaw("SUBSTRING(start_at, 1, 5) as start_at")
            ->pluck('start_at')
            ->toArray();

            $obj = [
                "day" => $value,
                "slot" => $slot,
            ];
            $slotsArray[] = $obj;
            $activeSlots[$value] = $slot;

        }
        $pageTitle = __('messages.slot', ['form' => __('messages.slot')]);

            return view('provider.edittimeslot', compact('slotsArray', 'pageTitle', 'activeDay', 'provider_id', 'activeSlots','provider'));



    }
}
