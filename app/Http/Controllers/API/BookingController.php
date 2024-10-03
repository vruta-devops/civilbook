<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\BookingDetailResource;
use App\Http\Resources\API\BookingRatingResource;
use App\Http\Resources\API\BookingResource;
use App\Http\Resources\API\HandymanRatingResource;
use App\Http\Resources\API\HandymanResource;
use App\Http\Resources\API\PostJobBiderResource;
use App\Http\Resources\API\PostJobRequestResource;
use App\Http\Resources\API\ServiceProofResource;
use App\Http\Resources\API\ServiceResource;
use App\Http\Resources\API\UserResource;
use App\Models\ApiLog;
use App\Models\Booking;
use App\Models\BookingActivity;
use App\Models\BookingHandymanMapping;
use App\Models\BookingProviderMapping;
use App\Models\BookingRating;
use App\Models\BookingServiceAddonMapping;
use App\Models\BookingStatus;
use App\Models\HandymanRating;
use App\Models\Payment;
use App\Models\PaymentHistory;
use App\Models\PostJobBid;
use App\Models\PostJobRequest;
use App\Models\ServiceProof;
use App\Models\User;
use App\Models\Wallet;
use Auth;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function getBookingList(Request $request)
    {
        if (!empty($request->user_type) && $request->user_type === 'handyman') {
            $bookingIds = BookingHandymanMapping::where('handyman_id', auth()->user()->id);
        } else {
            $bookingIds = BookingProviderMapping::where('provider_id', auth()->user()->id);
        }

        $bookingIds = $bookingIds->pluck('booking_id')->toArray();

        $booking = Booking::with('customer', 'provider', 'service', 'providerMappings.providers:id,display_name');

        if (!empty($bookingIds) && !empty($request->provider_id)) {
            $booking = $booking->where(function ($query) use ($bookingIds) {
                foreach ($bookingIds as $key => $bookingId) {
                    if ($key === 0)
                        $query->where('id', $bookingId);
                    else
                        $query->orWhere('id', $bookingId);
                }
            });
        }

        if (empty($request->provider_id)) {
            $booking = $booking->where('customer_id', auth()->user()->id);
        } else {
            if (empty($bookingIds)) {
                $booking = $booking->where('provider_id', $request->provider_id);
            }
        }
        // if($request->has('status') && isset($request->status)){
        //     $booking->where('status',$request->status);
        // }

        if ($request->has('status') && isset($request->status)) {
            $status = explode(',', $request->status);
            $booking->whereIn('status', $status);

        }

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $booking->where(function ($query) use ($search) {
                $query->where('bookings.id', 'LIKE', "%$search%")
                    ->orWhereHas('service', function ($serviceQuery) use ($search) {
                        $serviceQuery->where('name', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('provider', function ($providerQuery) use ($search) {
                        $providerQuery->where(function ($nameQuery) use ($search) {
                            $nameQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                                ->orWhere('email', 'LIKE', "%$search");
                        });
                    })
                    ->orWhereHas('customer', function ($userQuery) use ($search) {
                        $userQuery->where(function ($nameQuery) use ($search) {
                            $nameQuery->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"])
                                ->orWhere('email', 'LIKE', "%$search");
                        });
                    });
            });
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $booking->count();
            }
        }
        $orderBy = 'desc';
        if ($request->has('orderby') && !empty($request->orderby)) {
            $orderBy = $request->orderby;
        }

        $booking = $booking->orderBy('id', $orderBy)->paginate($per_page);
        $items = BookingResource::collection($booking);

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

    public function getBookingDetail(Request $request)
    {

        $id = $request->booking_id;

        $booking_data = Booking::with('customer', 'provider', 'service.postJobService', 'bookingRating', 'bookingPostJob', 'bookingAddonService', 'providerMappings.providers.getServiceRating', 'payment')->where('id', $id)->first();
        if ($booking_data == null) {
            $message = __('messages.booking_not_found');
            return comman_message_response($message, 400);
        }
        $booking_detail = new BookingDetailResource($booking_data);

        $rating_data = BookingRatingResource::collection($booking_detail->bookingRating->take(5));
        if (!empty($booking_detail->service)) {
            $service = new ServiceResource($booking_detail->service);
        } else {
            // $service = new ServiceResource();
        }
        $customer = new UserResource($booking_detail->customer);
        $provider_data = new UserResource($booking_detail->provider);
        $handyman_data = HandymanResource::collection($booking_detail->handymanAdded);

        $customer_review = null;
        if ($request->customer_id != null) {
            $customer_review = BookingRating::where('customer_id', $request->customer_id)->where('service_id', $booking_detail->service_id)->where('booking_id', $id)->first();
            if (!empty($customer_review)) {
                $customer_review = new BookingRatingResource($customer_review);
            }
        }

        $auth_user = auth()->user();
        if (count($auth_user->unreadNotifications) > 0) {
            $auth_user->unreadNotifications->where('data.id', $id)->markAsRead();
        }

        $booking_activity = BookingActivity::where('booking_id', $id)->get();
        $serviceProof = ServiceProofResource::collection(ServiceProof::with('service', 'handyman', 'booking')->where('booking_id', $id)->get());
        $post_job_object = null;
        if ($booking_data->type == 'user_post_job') {
            $post_job_object = new PostJobRequestResource($booking_data->bookingPostJob);
        }

        $bidderData = $postJobAttachments = [];

        if (!empty($booking_detail->service) && $booking_detail->service->postJobService->count() > 0) {
            $postJobRequestId = $booking_detail->service->postJobService[0]->post_request_id;

            $bidderData = PostJobBiderResource::collection(PostJobBid::with(['provider', 'provider.providerSlots'])->where('post_request_id', $postJobRequestId)
                ->where('provider_id', $booking_detail->provider->id)
                ->get());

            $postJobRequest = PostJobRequest::find($postJobRequestId);

            $postJobAttachments = getAttachments($postJobRequest->getMedia('post_job_attachment'), null);
        }

        $response = [
            'bid_data' => $bidderData,
            'post_job_attachments' => $postJobAttachments,
            'booking_detail' => $booking_detail,
            'service' => $service,
            'customer' => $customer,
            'booking_activity' => $booking_activity,
            'rating_data' => $rating_data,
            'handyman_data' => $handyman_data,
            'provider_data' => $provider_data,
            'coupon_data' => $booking_detail->couponAdded,
            'customer_review' => $customer_review,
            'service_proof' => $serviceProof,
            'post_request_detail' => $post_job_object
        ];

        return comman_custom_response($response);
    }

    public function saveBookingRating(Request $request)
    {
        $rating_data = $request->all();
        $result = BookingRating::updateOrCreate(['id' => $request->id], $rating_data);

        $message = __('messages.update_form', ['form' => __('messages.rating')]);
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.rating')]);
        }

        return comman_message_response($message);
    }

    public function deleteBookingRating(Request $request)
    {
        $user = \Auth::user();

        $book_rating = BookingRating::where('id', $request->id)->where('customer_id', $user->id)->delete();

        $message = __('messages.delete_form', ['form' => __('messages.rating')]);

        return comman_message_response($message);
    }

    public function bookingStatus(Request $request)
    {
        $booking_status = BookingStatus::where('status', 1)->orderBy('sequence')->get();
        return comman_custom_response($booking_status);
    }

    public function bookingUpdate(Request $request)
    {
        $data = $request->all();
        $id = $request->id;
        $data['start_at'] = isset($request->start_at) ? date('Y-m-d H:i:s', strtotime($request->start_at)) : null;
        $data['end_at'] = isset($request->end_at) ? date('Y-m-d H:i:s', strtotime($request->end_at)) : null;


        $bookingdata = Booking::find($id);
        if ($data['status'] == 'pending_approval') {
            $bookingdata->update(['status' => 'pending_approval']);
        }
        $apiLogData = [
            'api_request' => json_encode($data),
            'api_response' => json_encode($bookingdata),
            'api_name' => 'booking-pending-approval'
        ];
        ApiLog::insert($apiLogData);
        $auth_user = authSession();
        if ($auth_user->user_type != 'user' && $auth_user->user_type != 'handyman' && $bookingdata->status != 'pending' && $bookingdata->provider_id != $auth_user->id) {
            $msg = __('messages.already_in_status', ['status' => $data['status']]);
            if ($request->is('api/*')) {
                return comman_message_response($msg);
            } else {
                return redirect()->back()->withSuccess($msg);
            }
        }
        // print_r($auth_user);exit;
        if ($auth_user->user_type == 'provider' && $bookingdata->provider_id != $auth_user->id) {
            $data['provider_id'] = $auth_user->id;
        }
        $paymentdata = Payment::where('booking_id', $id)->first();
        if ($request->type == 'service_addon') {
            if ($request->has('service_addon') && $request->service_addon != null) {
                foreach ($request->service_addon as $serviceaddon) {
                    $get_addon = BookingServiceAddonMapping::where('id', $serviceaddon)->first();
                    $get_addon->status = 1;
                    $get_addon->update();
                }
                $message = __('messages.update_form', ['form' => __('messages.booking')]);

                if ($request->is('api/*')) {
                    return comman_message_response($message);
                }
            }
        }
        if ($request->has('service_addon') && $request->service_addon != null) {
            foreach ($request->service_addon as $serviceaddon) {
                $get_addon = BookingServiceAddonMapping::where('id', $serviceaddon)->first();
                $get_addon->status = 1;
                $get_addon->update();
            }
        }
        if ($data['status'] === 'hold') {
            if ($bookingdata->start_at == null && $bookingdata->end_at == null) {
                $duration_diff = $data['duration_diff'];
                $data['duration_diff'] = $duration_diff;
            } else {
                if ($bookingdata->status == $data['status']) {
                    $booking_start_date = $bookingdata->start_at;
                    $request_start_date = $data['start_at'];
                    if ($request_start_date > $booking_start_date) {
                        $msg = __('messages.already_in_status', ['status' => $data['status']]);
                        return comman_message_response($msg);
                    }
                } else {
                    $duration_diff = $bookingdata->duration_diff;

                    if ($bookingdata->start_at != null && $bookingdata->end_at != null) {
                        $new_diff = $data['duration_diff'];
                    } else {
                        $new_diff = $data['duration_diff'];
                    }
                    $data['duration_diff'] = $duration_diff + $new_diff;
                }
            }
        }
        if ($data['status'] === 'completed') {
            $duration_diff = $bookingdata->duration_diff;
            $new_diff = $data['duration_diff'];
            $data['duration_diff'] = $duration_diff + $new_diff;
            $duration_diff = $bookingdata->duration_diff;
            $duration_diff = $bookingdata->duration_diff;

        }
        $user = User::where('id', $bookingdata['customer_id'])->first();
        $activity_type = "";
        if ($bookingdata->status != $data['status']) {
            $activity_type = 'update_booking_status';
        }
        if ($data['status'] == 'cancelled') {
            $activity_type = 'cancel_booking';
            $advance_paid_amount = $data['advance_paid_amount'];

            $wallet = Wallet::where('user_id', $bookingdata->customer_id)->first();
            if ($wallet !== null) {
                $wallet_amount = $wallet->amount;
                $wallet->amount = $wallet_amount + $advance_paid_amount;
                $wallet->update();
            }
        }
        if ($data['status'] == 'rejected') {
            $type = 'booking_rejected';
            $notificationData = [
                'id' => $id,
                'booking_status' => $data['status'],
                'type' => $type,
                'subject' => $type,
                'message' => auth()->user()->first_name . " has been rejected your booking request",
            ];


            notificationSend($user, $type, $notificationData);
            if ($bookingdata->handymanAdded()->count() > 0) {
                $assigned_handyman_ids = $bookingdata->handymanAdded()->pluck('handyman_id')->toArray();
                $bookingdata->handymanAdded()->delete();
                $data['status'] = 'accept';
            }
        }
        if ($data['status'] == 'pending') {
            if ($bookingdata->handymanAdded()->count() > 0) {
                $bookingdata->handymanAdded()->delete();
            }
        }
        if ($data['status'] == 'accept') {

            $provider = auth()->user();
            $provider_id = auth()->user()->id;

            BookingProviderMapping::where('booking_id', $id)->where('provider_id', '!=', $provider_id)->delete();
            // if(isset($data['provider_id'])){
            //     $provider_id = $data['provider_id'];
            // }

            // $data['provider_id'] = $provider_id;
            // print_r($data);exit;

            if (default_earning_type() === 'commission') {

                $provider_id = (auth()->user()->user_type == 'provider') ? auth()->user()->id : $bookingdata->provider_id;
                $provider_wallet = Wallet::where('user_id', $provider_id)->first();
                $apiLogData = [
                    'api_request' => json_encode($provider_wallet),
                    'api_response' => $provider_id,
                    'api_name' => 'booking-update-provider-balance'
                ];
                ApiLog::insert($apiLogData);
                if ($provider_wallet) {
                    $amount = $provider_wallet->amount;
                    if ($amount < 0 || $amount < $bookingdata->amount) {
                        $message = __('messages.wallet_balance_error');
                        $status_code = 406;
                        return comman_message_response($message, $status_code);
                    }
                }
            }
            $type = 'booking_accept';
            $notificationData = [
                'id' => $id,
                'booking_status' => $data['status'],
                'type' => $type,
                'subject' => $type,
                'message' => $provider->first_name . " has been accepted your booking request",
            ];

            notificationSend($user, $type, $notificationData);
        }
        $data['reason'] = isset($data['reason']) ? $data['reason'] : null;
        $old_status = $bookingdata->status;
        $bookingdata->update($data);
        if ($old_status != $data['status']) {
            $bookingdata->status = $old_status;
            $activity_data = [
                'activity_type' => $activity_type,
                'booking_id' => $id,
                'booking' => $bookingdata,
            ];

            saveBookingActivity($activity_data);
        }
        if ($bookingdata->payment_id != null) {
            $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
            $paymentdata->update($data);

            if ($bookingdata->payment_id != null) {
                $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
                $paymentdata->update($data);
            }
        }

        if (($data['status'] == 'rejected' || $data['status'] == 'cancelled') && $data['payment_status'] == 'advanced_paid') {
            $advance_paid_amount = $bookingdata->advance_paid_amount;

            $user_wallet = Wallet::where('user_id', $bookingdata->customer_id)->first();

            $wallet_amount = $user_wallet->amount;

            $user_wallet->amount = $wallet_amount + $advance_paid_amount;

            $user_wallet->update();
            $activity_data = [
                'activity_type' => $activity_type,
                'wallet' => $user_wallet,
                'booking_id' => $id,
                'booking' => $bookingdata,
                'refund_amount' => $advance_paid_amount,
            ];

            saveWalletHistory($activity_data);
        }
        if ($bookingdata->payment_id != null) {
            $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
            $paymentdata->update($data);

            if ($bookingdata->payment_id != null) {
                $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
                $paymentdata->update($data);
            }
        }
        $totalExtraChargeAmount = 0;
        $overWriteTotalAmount = 0;
        $data['reason'] = isset($data['reason']) ? $data['reason'] : null;
        $old_status = $bookingdata->status;
        if (!empty($request->extra_charges)) {
            if ($bookingdata->bookingExtraCharge()->count() > 0) {
                $bookingdata->bookingExtraCharge()->delete();
            }
            foreach ($request->extra_charges as $extra) {
                $extra_charge = [
                    'title' => $extra['title'],
                    'price' => $extra['price'],
                    'qty' => $extra['qty'],
                    'booking_id' => $bookingdata->id,
                ];
                if (!empty($extra['is_overwrite'])) {
                    $totalExtraChargeAmount = $extra['price'];
                    $overWriteTotalAmount = 1;
                }

                $bookingdata->bookingExtraCharge()->insert($extra_charge);
            }
            $subtotal = $bookingdata->getSubTotalValue() + $bookingdata->getServiceAddonValue();
            $tax = $bookingdata->getTaxesValue();
            $totalamount = $subtotal + $bookingdata->getExtraChargeValue() + $tax;
            $data['total_amount'] = round($totalamount, 2);
            $data['final_total_tax'] = round($tax, 2);
        }
        if (!empty($request->extra_charges)) {
            $bookingDataUpdate['is_overwrite'] = $overWriteTotalAmount;
            $bookingdata->update($bookingDataUpdate);
        }
        $apiLogData = [
            'api_request' => json_encode($data),
            'api_response' => json_encode($bookingdata),
            'api_name' => 'booking-cancel'
        ];
        ApiLog::insert($apiLogData);

        if ($bookingdata->payment_id != null) {
            $payment_status = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
            $paymentdata->update(['payment_status' => $payment_status]);
        }

        if ($data['status'] == 'completed' && $data['payment_status'] == 'pending_by_admin') {
            $handyman = BookingHandymanMapping::where('booking_id', $bookingdata->id)->first();
            $user = User::where('id', $handyman->handyman_id)->first();
            $payment_history = [
                'payment_id' => $paymentdata->id,
                'booking_id' => $paymentdata->booking_id,
                'type' => $paymentdata->payment_type,
                'sender_id' => $bookingdata->customer_id,
                'receiver_id' => $handyman->handyman_id,
                'total_amount' => ($totalExtraChargeAmount > 0) ? $totalExtraChargeAmount : $paymentdata->total_amount,
                'datetime' => date('Y-m-d H:i:s'),
                'text' => __('messages.payment_transfer', ['from' => get_user_name($bookingdata->customer_id), 'to' => get_user_name($handyman->handyman_id),
                    'amount' => getPriceFormat((float)$paymentdata->total_amount)]),
            ];
            if ($user->user_type == 'provider') {
                $payment_history['status'] = config('constant.PAYMENT_HISTORY_STATUS.APPROVED_PROVIDER');
                $payment_history['action'] = 'handyman_send_provider';
            } else {
                $payment_history['status'] = config('constant.PAYMENT_HISTORY_STATUS.APPRVOED_HANDYMAN');
                $payment_history['action'] = config('constant.PAYMENT_HISTORY_ACTION.HANDYMAN_APPROVED_CASH');
            }
            if (!empty($paymentdata->txn_id)) {
                $payment_history['txn_id'] = $paymentdata->txn_id;
            }
            if (!empty($paymentdata->other_transaction_detail)) {
                $payment_history['other_transaction_detail'] = $paymentdata->other_transaction_detail;
            }
            $res = PaymentHistory::create($payment_history);
            $res->parent_id = $res->id;
            $res->update();
        }
        $message = __('messages.update_form', ['form' => __('messages.booking')]);

        if ($request->is('api/*')) {
            return comman_message_response($message);
        }
    }

    public function saveHandymanRating(Request $request)
    {
        $user = auth()->user();
        $rating_data = $request->all();
        $rating_data['customer_id'] = $user->id;
        $result = HandymanRating::updateOrCreate(['id' => $request->id], $rating_data);

        $message = __('messages.update_form', ['form' => __('messages.rating')]);
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.rating')]);
        }

        return comman_message_response($message);
    }

    public function getHandymanRatingList(Request $request)
    {

        $handymanratings = HandymanRating::orderBy('id', 'desc');

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $handymanratings->count();
            }
        }

        $handymanratings = $handymanratings->paginate($per_page);
        $data = HandymanRatingResource::collection($handymanratings);

        return response([
            'pagination' => [
                'total_ratings' => $data->total(),
                'per_page' => $data->perPage(5),
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'next_page' => $data->nextPageUrl(),
                'previous_page' => $data->previousPageUrl(),
            ],
            'data' => $data,
        ]);
    }

    public function deleteHandymanRating(Request $request)
    {
        $user = auth()->user();

        $book_rating = HandymanRating::where('id', $request->id)->where('customer_id', $user->id)->delete();

        $message = __('messages.delete_form', ['form' => __('messages.rating')]);

        return comman_message_response($message);
    }

    public function bookingRatingByCustomer(Request $request)
    {
        $customer_review = null;
        if ($request->customer_id != null) {
            $customer_review = BookingRating::where('customer_id', $request->customer_id)->where('service_id', $request->service_id)->where('booking_id', $request->booking_id)->first();
            if (!empty($customer_review)) {
                $customer_review = new BookingRatingResource($customer_review);
            }
        }
        return comman_custom_response($customer_review);

    }

    public function uploadServiceProof(Request $request)
    {
        $booking = $request->all();
        $result = ServiceProof::create($booking);
        if ($request->has('attachment_count')) {
            for ($i = 0; $i < $request->attachment_count; $i++) {
                $attachment = "booking_attachments_" . $i;
                if ($request->$attachment != null) {
                    $file[] = $request->$attachment;
                }
            }
            storeMediaFile($result, $file, 'booking_attachments');
        }
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.attachments')]);
        }
        return comman_message_response($message);
    }

    public function getUserRatings(Request $request)
    {
        $user = auth()->user();
        $user_id = $user->id;
        $ratings = BookingRating::where('customer_id', $user_id)->get();

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $ratings->count();
            }
        }

        $ratings = BookingRating::where('customer_id', $user_id)->paginate($per_page);
        $data = BookingRatingResource::collection($ratings);

        return response([
            'pagination' => [
                'total_ratings' => $data->total(),
                'per_page' => $data->perPage(5),
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'next_page' => $data->nextPageUrl(),
                'previous_page' => $data->previousPageUrl(),
            ],
            'data' => $data,
        ]);
    }

    public function getRatingsList(Request $request)
    {
        $type = $request->type;

        if ($type === 'user_service_rating') {
            $user = auth()->user();

            if (auth()->user() !== null) {

                if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('demo_admin')) {
                    $ratings = BookingRating::orderBy('id', 'desc');
                } else {
                    $ratings = BookingRating::where('customer_id', $user->id)->orderBy('id', 'desc');
                }
            }
        } elseif ($type === 'handyman_rating') {
            $ratings = HandymanRating::orderBy('id', 'desc');
        } else {
            return response()->json(['message' => 'Invalid type parameter'], 400);
        }

        $per_page = config('constant.PER_PAGE_LIMIT');
        if ($request->has('per_page') && !empty($request->per_page)) {
            if (is_numeric($request->per_page)) {
                $per_page = $request->per_page;
            }
            if ($request->per_page === 'all') {
                $per_page = $ratings->count();
            }
        }

        $ratings = $ratings->paginate($per_page);
        $data = HandymanRatingResource::collection($ratings);

        return response([
            'pagination' => [
                'total_ratings' => $data->total(),
                'per_page' => $data->perPage(5),
                'currentPage' => $data->currentPage(),
                'totalPages' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'next_page' => $data->nextPageUrl(),
                'previous_page' => $data->previousPageUrl(),
            ],
            'data' => $data,
        ]);
    }

    public function deleteRatingsList($id, Request $request)
    {
        $type = $request->type;

        if (demoUserPermission()) {
            $message = __('messages.demo.permission.denied');
            return comman_message_response($message);
        }
        if ($type === 'user_service_rating') {
            $bookingrating = BookingRating::find($id);
            $msg = __('messages.msg_fail_to_delete', ['name' => __('messages.user_ratings')]);

            if ($bookingrating != '') {
                $bookingrating->delete();
                $msg = __('messages.msg_deleted', ['name' => __('messages.user_ratings')]);
            }
        } elseif ($type === 'handyman_rating') {
            $handymanrating = HandymanRating::find($id);
            $msg = __('messages.msg_fail_to_delete', ['name' => __('messages.handyman_ratings')]);

            if ($handymanrating != '') {
                $handymanrating->delete();
                $msg = __('messages.msg_deleted', ['name' => __('messages.handyman_ratings')]);
            }
        } else {
            $msg = "Invalid type parameter";
            return comman_custom_response(['message' => $msg, 'status' => false]);
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }
}
