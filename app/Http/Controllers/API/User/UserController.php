<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePlayerIdRequest;
use App\Http\Requests\UserRequest;
use App\Http\Resources\API\DepartmentResource;
use App\Http\Resources\API\HandymanRatingResource;
use App\Http\Resources\API\ServiceResource;
use App\Http\Resources\API\UserResource;
use App\Models\Booking;
use App\Models\Department;
use App\Models\HandymanRating;
use App\Models\ProviderAddressMapping;
use App\Models\ProviderCategoryMapping;
use App\Models\ReportUser;
use App\Models\Service;
use App\Models\SubCategory;
use App\Models\User;
use App\Models\UserPlayerIds;
use App\Models\Wallet;
use App\Notifications\PasswordResetNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Mail;
use Throwable;
use Validator;

class UserController extends Controller
{

    public function register(UserRequest $request)
    {
        $input = $request->all();
        $email = $input['email'];
        $username = $input['username'];
        $password = $input['password'];
        $input['display_name'] = $input['first_name'] . " " . $input['last_name'];
        $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
        $input['password'] = Hash::make($password);

        if (in_array($input['user_type'], ['handyman', 'provider'])) {
            $input['status'] = isset($input['status']) ? $input['status'] : 0;
        }

        $user = User::withTrashed()
            ->where('login_type', $input['login_type'])
            ->where(function ($query) {
                $userType = request('user_type');
                if ($userType === 'provider') {
                    return $query->whereIn('user_type', [$userType, 'handyman']);
                }

                return $query->where('user_type', $userType);
            })
            ->where(function ($query) use ($email, $username) {
                if (!empty(trim($email))) {
                    $query->where('email', $email)->orWhere('username', $username);
                } else {
                    $query->where('username', $username);
                }
            })
            ->first();

        $providerCategories = [];
        if ($user) {
            if ($user->deleted_at == null) {

                $message = trans('messages.login_form');
                $response = [
                    'message' => $message,
                ];
                if(!empty($input['request_from']) && $input['request_from'] = 'web'){
                    return back()->withErrors([
                        'email' =>  $message
                    ]);

                }
                else{
                    return comman_custom_response($response);
                }
            }
            $message = trans('messages.deactivate');
            $response = [
                'message' => $message,
                'Isdeactivate' => 1,
            ];
            if (!empty($input['request_from']) && $input['request_from'] == 'web') {
                return back()->withErrors([
                    'email' =>  $message
                ]);
            }
            else{
                return comman_custom_response($response);
            }
        } else {
            if ($input['user_type'] == 'provider') {
                $providerAddress = [
                    'address' => $input['address'],
                    'latitude' => $input['latitude'],
                    'longitude' => $input['longitude'],
                ];

                $providerCategories = $input['categories'];

                $providerSubCategories = ((empty($input['sub_categories']) || $input['sub_categories'][0] == 0)) ? [] : $input['sub_categories'];

                unset($input['latitude']);
                unset($input['longitude']);
                unset($input['categories']);
                unset($input['sub_categories']);
            }
            $input['email'] = !empty($input['email']) ? $input['email'] : null;
            $input['uid'] = Str::uuid()->toString();
            $user = User::create($input);
            if ($user->user_type === 'provider') {
                $type = 'new_provider_register';
                $notificationData = [
                    'id' => $user->id,
                    'type' => $type,
                    'user_name' => $user->first_name,
                    'subject' => $type,
                    'message' => $user->first_name . " has been register as provider. Please approve/reject their account",
                ];

                saveAdminNotification($notificationData);
            }

            if (!empty($request->profile_image)) {
                storeMediaFile($user, $request->profile_image, 'profile_image');
            }

            $user->assignRole($input['user_type']);
        }

        if ($user->user_type == 'provider' || $user->user_type == 'user') {
            if ($user->user_type == 'provider') {
                $providerAddress['provider_id'] = $user->id;

                ProviderAddressMapping::create($providerAddress);
                $subCategories = new SubCategory();

                if (!empty($providerSubCategories) && $providerSubCategories != '[0]') {
                    $subCategories = $subCategories->whereIn('id', explode(",", $providerSubCategories));
                } else {
                    $subCategories = $subCategories->whereIn('category_id', explode(",", $providerCategories));
                }


                $subCategories = $subCategories->get();
                $providerSubCategoriesMapping = [];
                $currentTimeStamp = Carbon::now();

                foreach ($subCategories as $index => $subCategory) {
                    $providerSubCategoriesMapping[] = [
                        'provider_id' => $user->id,
                        'category_id' => $subCategory->category->id,
                        'sub_category_id' => $subCategory->id,
                        'created_at' => $currentTimeStamp,
                        'deleted_at' => $currentTimeStamp,
                        'is_category_all' => 0,
                        'is_sub_category_all' => 0,
                    ];
                }
                if (!empty($providerSubCategoriesMapping)) {
                    ProviderCategoryMapping::insert($providerSubCategoriesMapping);
                }
            }

            $wallet = array(
                'title' => $user->display_name,
                'user_id' => $user->id,
                'amount' => 0
            );
            $result = Wallet::create($wallet);
        }
        if (!empty($input['loginfrom']) && $input['loginfrom'] === 'vue-app') {
            if ($user->user_type != 'user') {
                $message = trans('messages.save_form', ['form' => $input['user_type']]);
                $response = [
                    'message' => $message,
                    'data' => $user
                ];
                return comman_custom_response($response);
            }
        }
        $input['api_token'] = $user->createToken('auth_token')->plainTextToken;

        unset($input['password']);
        $message = trans('messages.save_form', ['form' => $input['user_type']]);

        $user->api_token = $user->createToken('auth_token')->plainTextToken;
        unset($user->preferred_location_distance);
        $response = [
            'message' => $message,
            'data' => $user
        ];
        if(!empty($input['request_from']) && $input['request_from'] = 'web'){

            if($input['user_type'] == 'provider'){
                return redirect(route('login'));
            }
            Auth::login($user);
            return redirect(route('home'));
        }
        else{
            return comman_custom_response($response);
        }
    }

    public function userList(Request $request)
    {
        $user_type = isset($request['user_type']) ? $request['user_type'] : 'handyman';
        $status = isset($request['status']) ? $request['status'] : 1;

        $user_list = User::orderBy('id', 'desc')->where('user_type', $user_type);
        if (!empty($status)) {
            $user_list = $user_list->where('status', $status);
        }

        if (default_earning_type() === 'subscription' && $user_type == 'provider' && auth()->user() !== null && !auth()->user()->hasRole('admin')) {
            $user_list = $user_list->where('is_subscribe', 1);
        }

        if (auth()->user() !== null && auth()->user()->hasRole('admin')) {
            $user_list = $user_list->withTrashed();
            if ($request->has('keyword') && isset($request->keyword)) {
                $user_list = $user_list->where('display_name', 'like', '%' . $request->keyword . '%');
            }
            if ($user_type == 'handyman' && $status == 0) {
                $user_list = $user_list->orWhere('provider_id', NULL)->where('user_type', 'handyman');
            }
            if ($user_type == 'handyman' && $status == 1) {
                $user_list = $user_list->whereNotNull('provider_id')->where('user_type', 'handyman');
            }

        }
        if ($request->has('provider_id')) {
            $user_list = $user_list->where('provider_id', $request->provider_id);
        }
        if ($request->has('city_id') && !empty($request->city_id)) {
            $user_list = $user_list->where('city_id', $request->city_id);
        }
        if ($request->has('status') && isset($request->status)) {
            $user_list = $user_list->where('status', $request->status);
        }
        if ($request->has('keyword') && isset($request->keyword)) {
            $user_list = $user_list->where('display_name', 'like', '%' . $request->keyword . '%');
        }
        if ($request->has('booking_id')) {
            $booking_data = Booking::find($request->booking_id);

            $service_address = $booking_data->handymanByAddress;
            if ($service_address != null) {
                $user_list = $user_list->where('service_address_id', $service_address->id);
            }
        }
        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $user_list->count();
            }
        }

        $user_list = $user_list->paginate($per_page);

        $items = UserResource::collection($user_list);

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

    public function userDetail(Request $request)
    {
        $id = $request->id;

        $user = User::find($id);
        $message = __('messages.detail');
        if (empty($user)) {
            $message = __('messages.user_not_found');
            return comman_message_response($message, 400);
        }

        $service = [];
        $handyman_rating = [];

        if ($user->user_type == 'provider') {
            $service = Service::where('provider_id', $id)->where('status', 1)->where('user_service_status', 1)->orderBy('id', 'desc')->paginate(10);
            $service = ServiceResource::collection($service);
            $handyman_rating = HandymanRating::where('handyman_id', $id)->orderBy('id', 'desc')->paginate(10);
            $handyman_rating = HandymanRatingResource::collection($handyman_rating);
        }
        $user_detail = new UserResource($user);
        if ($user->user_type == 'handyman') {
            $handyman_rating = HandymanRating::where('handyman_id', $id)->orderBy('id', 'desc')->paginate(10);
            $handyman_rating = HandymanRatingResource::collection($handyman_rating);
        }

        $response = [
            'data' => $user_detail,
            'service' => $service,
            'handyman_rating_review' => $handyman_rating
        ];
        return comman_custom_response($response);

    }

    public function changePassword(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->first();

        if ($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message, 400);
        }

        $hashedPassword = $user->password;

        $match = Hash::check($request->old_password, $hashedPassword);

        $same_exits = Hash::check($request->new_password, $hashedPassword);
        if ($match) {
            if ($same_exits) {
                $message = __('messages.old_new_pass_same');
                return comman_message_response($message, 400);
            }

            $user->fill([
                'password' => Hash::make($request->new_password)
            ])->save();

            $message = __('messages.password_change');
            return comman_message_response($message, 200);
        } else {
            $message = __('messages.valid_password');
            return comman_message_response($message);
        }
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if ($request->has('id') && !empty($request->id)) {
            $user = User::where('id', $request->id)->first();
        }
        if ($user == null) {
            return comman_message_response(__('messages.no_record_found'), 400);
        }

        $user->fill($request->all())->update();
        $data = $request->all();

        $why_choose_me = [

            'why_choose_me_title' => $request->why_choose_me_title,
            'why_choose_me_reason' => isset($request->why_choose_me_reason) && is_string($request->why_choose_me_reason)
                ? array_filter(json_decode($request->why_choose_me_reason), function ($value) {
                    return $value !== null;
                })
                : null,

        ];

        $data['why_choose_me'] = ($why_choose_me);

        $user->fill($data)->update();

        if (isset($request->profile_image) && $request->profile_image != null) {
            $user->clearMediaCollection('profile_image');
            storeMediaFile($user, $request->profile_image, 'profile_image');
        }

        $user_data = User::find($user->id);

        if (request('player_id') != null) {
            $get_player_id = UserPlayerIds::where('user_id', $user->id)->pluck('player_id');

            if (!in_array(request('player_id'), $get_player_id->toArray())) {
                $data = [
                    'user_id' => $user->id,
                    'player_id' => request('player_id'),
                ];
                UserPlayerIds::create($data);

            }

        }
        $message = __('messages.updated');
        $user_data['profile_image'] = getSingleMedia($user_data, 'profile_image', null);
        $user_data['user_role'] = $user->getRoleNames();
        $user_data['player_ids'] = $user_data->playerids->pluck('player_id');

        unset($user_data['roles']);
        unset($user_data['media']);
        unset($user_data->playerids);
        $response = [
            'data' => $user_data,
            'message' => $message
        ];
        return comman_custom_response($response);
    }

    public function logout(Request $request)
    {
        $auth = Auth::user();
        if ($request->is('api*')) {
            $auth->player_id = null;
            $auth->save();
            return comman_message_response('Logout successfully');
        }
        if (request('player_id') !== null) {
            $user = UserPlayerIds::where('user_id', $auth->id)->where('player_id', request('player_id'))->get();
            if ($request->is('api*')) {
                $user->each(function ($record) {
                    $record->delete();
                });
                return comman_message_response('Logout successfully');
            }
        }
        return comman_message_response('Logout successfully');

    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'user_type' => 'required'
        ]);
        $data = $request->all();
        $user = User::where('email', $data['email'])
            ->where('user_type', $data['user_type'])
            ->where('login_type', 'normal')
            ->first();

        if (empty($user)) {
            return response()->json(['message' => __('messages.email_not_found'), 'status' => false], 400);
        }
        $token = Password::createToken($user);

        $url = URL::route('password.reset', ['token' => $token]) . '?email=' . urlencode($data['email']) . "&user_type=" . $data['user_type'];

        $user->notify(new PasswordResetNotification($url));
        return response()->json(['message' => __('messages.password_reset_mail_send'), 'status' => true]);
    }

    public function socialLogin(Request $request)
    {
        $input = $request->all();
        infoLog("login data", $input);
        $userType = request('user_type');

        $userEmail = User::where(function ($query) use ($userType) {
            if ($userType === 'provider') {
                return $query->whereIn('user_type', [$userType, 'handyman']);
            }

            return $query->where('user_type', $userType);
        })->where(function ($query) use ($input) {
            if ($input['login_type'] === 'mobile') {
                if (!empty($input['is_mobile_login']) && $input['is_mobile_login'] == 1) {
                    return $query->where('contact_number', 'like', '%' . $input['username'] . '%')->where('login_type', 'mobile');
                } else {
                    return $query->where('username', $input['username'])->where('login_type', 'mobile');
                }
            } else {
                return $query->where('email', $input['email'])->where('login_type', 'google');
            }
        })->first();

        if (empty($userEmail)) {
            $message = $input['login_type'] === 'mobile' ? trans('auth.mobile_no_not_exist') : trans('auth.email_not_exist');
            return comman_message_response($message, 407);
        }

        $user_data = User::where('login_type', $input['login_type'])->where('user_type', $userType);

        if ($input['login_type'] === 'mobile') {
            $user_data = $user_data->where('contact_number', 'like', '%' . $input['username'] . '%')->where('login_type', 'mobile')->first();
        } else {
            $user_data = $user_data->where('email', $input['email'])->first();

        }

        if ($user_data != null) {
            //            if( !isset($user_data->login_type) || $user_data->login_type  == '' ){
            //                if($request->login_type === 'google'){
            //                    $message = __('validation.unique',['attribute' => 'email' ]);
            //                } else {
            //                    $message = __('validation.unique',['attribute' => 'username' ]);
            //                }
            //                return comman_message_response($message,400);
            //            }
            if (empty($user_data->uid)) {
                $user_data->uid = Str::uuid()->toString();
                $user_data->save();
            }

            $message = __('messages.login_success');
        } else {

            if ($request->login_type === 'google') {
                $key = 'email';
                $value = $request->email;
            } else {
                $key = 'username';
                $value = $request->username;
            }

            $trashed_user_data = User::where($key, $value)->whereNotNull('login_type')->withTrashed()->first();


            if ($request->login_type === 'mobile' && $user_data == null) {
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => false
                ];
                return comman_custom_response($otp_response);
            }
            if ($request->login_type === 'mobile' && $user_data != null) {
                $otp_response = [
                    'status' => true,
                    'is_user_exist' => true
                ];
                return comman_custom_response($otp_response);
            }

            $password = !empty($input['accessToken']) ? $input['accessToken'] : $input['email'];

            $input['user_type'] = "user";
            $input['display_name'] = $input['first_name'] . " " . $input['last_name'];
            $input['password'] = Hash::make($password);
            $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
            if (request('player_id') != null) {
                $input['player_id'] = request('player_id');
            }
            if (empty($input['uid'])) {
                $input['uid'] = Str::uuid()->toString();
            }
            $user = User::updateOrCreate($input);
            if (request('player_id') != null) {
                $data = [
                    'user_id' => $user->id,
                    'player_id' => request('player_id'),
                ];
                UserPlayerIds::create($data);

            }
            $user->assignRole($input['user_type']);

            $user_data = User::where('id', $user->id)->first();
            $message = trans('messages.save_form', ['form' => $input['user_type']]);
        }

        if (request('player_id') != null) {
            $user_data->player_id = request('player_id');
            $user_data->save();
        }
        $user_data['api_token'] = $user_data->createToken('auth_token')->plainTextToken;
        $user_data['profile_image'] = getSingleMedia($user_data, 'profile_image', null);
        $user_data['sent_requests'] = getTotalRequests($user_data['id']);
        $user_data['expired_requests'] = getTotalExpiredRequests($user_data['id']);
        $user_data['placed_work_orders'] = getTotalOrders($user_data['id']);
        $user_data['department'] = !empty($user_data->department_id) ? new DepartmentResource(Department::find($user_data->department_id)) : null;
        $response = [
            'status' => true,
            'message' => $message,
            'data' => $user_data
        ];
        return comman_custom_response($response);
    }

    public function userStatusUpdate(Request $request)
    {
        $user_id = $request->id;
        $user = User::where('id', $user_id)->first();

        if ($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message, 400);
        }
        $user->status = $request->status;
        $user->save();

        $message = __('messages.update_form', ['form' => __('messages.status')]);
        $response = [
            'data' => new UserResource($user),
            'message' => $message
        ];
        return comman_custom_response($response);
    }

    public function contactUs(Request $request)
    {
        try {
            Mail::send('contactus.contact_email',
                array(
                    'first_name' => $request->get('first_name'),
                    'last_name' => $request->get('last_name'),
                    'email' => $request->get('email'),
                    'subject' => $request->get('subject'),
                    'phone_no' => $request->get('phone_no'),
                    'user_message' => $request->get('user_message'),
                ), function ($message) use ($request) {
                    $message->from($request->email);
                    $message->to(env('MAIL_FROM_ADDRESS'));
                });
            $messagedata = __('messages.contact_us_greetings');
            return comman_message_response($messagedata);
        } catch (Throwable $th) {
            $messagedata = __('messages.something_wrong');
            return comman_message_response($messagedata);
        }

    }

    public function handymanAvailable(Request $request)
    {
        $user_id = $request->id;
        $user = User::where('id', $user_id)->first();

        if ($user == "") {
            $message = __('messages.user_not_found');
            return comman_message_response($message, 400);
        }
        $user->is_available = $request->is_available;
        $user->save();

        $message = __('messages.update_form', ['form' => __('messages.status')]);
        $response = [
            'data' => new UserResource($user),
            'message' => $message
        ];
        return comman_custom_response($response);
    }

    public function handymanReviewsList(Request $request)
    {
        $id = $request->handyman_id;
        $handyman_rating_data = HandymanRating::where('handyman_id', $id);

        $per_page = config('constant.PER_PAGE_LIMIT');

        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $handyman_rating_data->count();
            }
        }

        $handyman_rating_data = $handyman_rating_data->orderBy('created_at', 'desc')->paginate($per_page);

        $items = HandymanRatingResource::collection($handyman_rating_data);
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

    public function deleteUserAccount(Request $request)
    {
        $user_id = Auth::user()->id;
        $user = User::where('id', $user_id)->first();
        if ($user == null) {
            $message = __('messages.user_not_found');
            __('messages.msg_fail_to_delete', ['item' => __('messages.user')]);
            return comman_message_response($message, 400);
        }
        $user->booking()->forceDelete();
        $user->payment()->forceDelete();
        $user->forceDelete();
        $message = __('messages.msg_deleted', ['name' => __('messages.user')]);
        return comman_message_response($message, 200);
    }

    public function deleteAccount(Request $request)
    {
        $user_id = Auth::user()->id;
        $user = User::where('id', $user_id)->first();
        if ($user == null) {
            $message = __('messages.user_not_found');
            __('messages.msg_fail_to_delete', ['item' => __('messages.user')]);
            return comman_message_response($message, 400);
        }
        if ($user->user_type == 'provider') {
            if ($user->providerPendingBooking()->count() == 0) {
                $user->providerService()->forceDelete();
                $user->providerPendingBooking()->forceDelete();
                $provider_handyman = User::where('provider_id', $user_id)->get();
                if (count($provider_handyman) > 0) {
                    foreach ($provider_handyman as $key => $value) {
                        $value->provider_id = NULL;
                        $value->update();
                    }
                }
                $user->forceDelete();
            } else {
                $message = __('messages.pending_booking');
                return comman_message_response($message, 400);
            }
        } else {
            if ($user->handymanPendingBooking()->count() == 0) {
                $user->handymanPendingBooking()->forceDelete();
                $user->forceDelete();
            } else {
                $message = __('messages.pending_booking');
                return comman_message_response($message, 400);
            }
        }
        $message = __('messages.msg_deleted', ['name' => __('messages.user')]);
        return comman_message_response($message, 200);
    }

    public function addUser(UserRequest $request)
    {
        $input = $request->all();

        $password = $input['password'];
        $input['display_name'] = $input['first_name'] . " " . $input['last_name'];
        $input['user_type'] = isset($input['user_type']) ? $input['user_type'] : 'user';
        $input['password'] = Hash::make($password);

        if ($input['user_type'] === 'provider') {
        }
        $user = User::create($input);
        $user->assignRole($input['user_type']);
        $input['api_token'] = $user->createToken('auth_token')->plainTextToken;

        unset($input['password']);
        $message = trans('messages.save_form', ['form' => $input['user_type']]);
        $user->api_token = $user->createToken('auth_token')->plainTextToken;
        $response = [
            'message' => $message,
            'data' => $user
        ];
        return comman_custom_response($response);
    }

    public function editUser(UserRequest $request)
    {
        if ($request->has('id') && !empty($request->id)) {
            $user = User::where('id', $request->id)->first();
        }
        if ($user == null) {
            return comman_message_response(__('messages.no_record_found'), 400);
        }

        $user->fill($request->all())->update();

        if (isset($request->profile_image) && $request->profile_image != null) {
            $user->clearMediaCollection('profile_image');
            $user->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }

        $user_data = User::find($user->id);

        $message = __('messages.updated');
        $user_data['profile_image'] = getSingleMedia($user_data, 'profile_image', null);
        $user_data['user_role'] = $user->getRoleNames();
        unset($user_data['roles']);
        unset($user_data['media']);
        $response = [
            'data' => $user_data,
            'message' => $message
        ];
        return comman_custom_response($response);
    }

    public function userWalletBalance(Request $request)
    {
        $user = Auth::user();
        $amount = 0;
        $wallet = Wallet::where('user_id', $user->id)->first();
        if ($wallet !== null) {
            $amount = $wallet->amount;
        }
        $response = [
            'balance' => $amount,
        ];
        return comman_custom_response($response);
    }

    public function socialLoginWeb(Request $request)
    {
        $input = $request->only(['phone']);
        $userType = getUserType($request->user_type);

        $userEmail = User::where('contact_number', 'like', '%' . $input['phone'] . '%')->where('user_type', $userType)->first();

        if (empty($userEmail)) {
            $message = trans('auth.mobile_no_not_exist');
            session()->put('mobile', $input['phone']);
            return comman_message_response($message, 201);
        } else {
            Auth::login($userEmail);
            $otp_response = [
                'status' => true,
                'is_user_exist' => true,
                'redirect_url' => route('home')
            ];
            return comman_message_response($otp_response);
        }
    }

    public function login()
    {
        $Isactivate = request('Isactivate');
        if ($Isactivate == 1) {
            $user = User::withTrashed()
                ->where('email', request('email'))
                ->first();
            if ($user) {
                $user->restore();
            } else {
                $message = trans('auth.failed');
                return comman_message_response($message, 406);
            }

        }
        $userType = request('user_type');
        $userEmail = User::where('email', request('email'))->where('login_type', 'normal')->where(function ($query) use ($userType) {

            if ($userType === 'provider') {
                return $query->whereIn('user_type', [$userType, 'handyman']);
            }

            return $query->where('user_type', $userType);
        })->first();

        if (empty($userEmail)) {
            $message = trans('auth.email_not_exist');
            return comman_message_response($message, 407);
        }

        $user = User::where('email', request('email'))->where('user_type', $userType)->where('login_type', 'normal')->first();

        if (!empty($user) && Hash::check(request('password'), $user->password)) {
            if (request('loginfrom') === 'vue-app') {
                if ($user->user_type != 'user') {
                    $message = trans('auth.not_able_login');
                    return comman_message_response($message, 400);
                }
            }
            if (request('player_id') != null) {
                $user->player_id = request('player_id');
            }

            if (empty($user->uid)) {
                $user->uid = Str::uuid()->toString();
            }

            $user->save();

            if (request('player_id') != null) {
                $data = [
                    'user_id' => $user->id,
                    'player_id' => request('player_id'),
                ];
                UserPlayerIds::create($data);

            }
            $success = $user;
            $success['user_role'] = $user->getRoleNames();
            $success['api_token'] = $user->createToken('auth_token')->plainTextToken;
            $success['profile_image'] = getSingleMedia($user, 'profile_image', null);
            $is_verify_provider = false;

            if ($user->user_type == 'provider') {

                $is_verify_provider = verify_provider_document($user->id);
                $success['subscription'] = get_user_active_plan($user->id);
                $success['department'] = empty($user->department_id) ? null : new DepartmentResource(Department::find($user->department_id));
                if (is_any_plan_active($user->id) == 0 && $success['is_subscribe'] == 0) {
                    $success['subscription'] = user_last_plan($user->id);
                }
                $success['is_subscribe'] = is_subscribed_user($user->id);
                $success['provider_id'] = admin_id();

            }

            if ($user->user_type == 'provider' || $user->user_type == 'user') {
                $wallet = Wallet::where('user_id', $user->id)->first();
                if ($wallet == null) {
                    $wallet = array(
                        'title' => $user->display_name,
                        'user_id' => $user->id,
                        'amount' => 0
                    );
                    Wallet::create($wallet);
                }
            }
            $success['is_verify_provider'] = (int)$is_verify_provider;
            unset($success['media']);
            unset($user['roles']);

            $success['sent_requests'] = getTotalRequests($success['id']);
            $success['expired_requests'] = getTotalExpiredRequests($success['id']);
            $success['placed_work_orders'] = getTotalOrders($success['id']);

            $success['player_ids'] = $user->playerids->pluck('player_id');
            unset($user->playerids);

            return response()->json(['data' => $success], 200);
        } else {
            $message = trans('auth.failed');
            return comman_message_response($message, 406);
        }
    }

    public function emailLoginWeb(Request $request)
    {
        $input = $request->all();

        $userType = getUserType($input['user_type']);

        $userEmail = User::where('email', $input['email'])->where('user_type', $userType)->first();

        if (empty($userEmail)) {
            // Authentication failed
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        } else {
            $credentials = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
            $credentials['user_type'] = $userType;
            $credentials['login_type'] = 'normal';
            // Attempt to authenticate the user
            if (Auth::attempt($credentials)) {
                // Redirect the authenticated user to the intended page
                return redirect(route('home'));
            } else {
                // Authentication failed
                return back()->withErrors([
                    'email' => 'The provided credentials do not match our records.',
                ]);
            }
        }
    }

    public function reportUser(Request $request)
    {
        $data = $request->all();

        ReportUser::create([
            'user_id' => $data['user_id'],
            'reported_by' => auth()->user()->id,
            'reason' => $data['reason']
        ]);

        return comman_message_response("User has been reported.!", 201);
    }

    public function updatePlayerId(UpdatePlayerIdRequest $request)
    {
        $user = User::where('id', getLoggedUserId())->first();

        $user->player_id = $request->player_id;
        $user->save();

        return comman_message_response("Player id has been updated successfully", 201);
    }
}
