<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\AudioServiceRequestResource;
use App\Http\Resources\API\PostJobBiderResource;
use App\Http\Resources\API\PostJobRequestDetailResource;
use App\Http\Resources\API\PostJobRequestResource;
use App\Models\ApiLog;
use App\Models\AudioServiceRequest;
use App\Models\Booking;
use App\Models\PostJobBid;
use App\Models\PostJobRequest;
use App\Models\PostRequestStatus;
use App\Models\ProviderAddressMapping;
use App\Models\ProviderCategoryMapping;
use Illuminate\Http\Request;

class PostJobRequestController extends Controller
{

    public function postRequestStatus(Request $request)
    {
        $post_job_status = PostRequestStatus::orderBy('sequence')->get();
        return comman_custom_response($post_job_status);
    }
    public function getPostRequestList(Request $request){
        $user = auth()->user();

        if (default_earning_type() === 'subscription') {
            if ($user->is_subscribe != 1 && $user->user_type == 'provider') {
                return comman_custom_response(['data' => []]);
            }
        }
        $post_request = PostJobRequest::whereIn('status', ['requested', 'accepted', 'assigned']);

        $loginType = getLoggedUserType();
        $loginId = getLoggedUserId();

        if ($loginType == 'user') {
            $post_request = $post_request->where('customer_id', $loginId);
        }

        if ($loginType == 'provider') {
            $providerAddresses = ProviderAddressMapping::where('provider_id', $loginId)->get();
            $providerSubCategories = ProviderCategoryMapping::where('provider_id', $loginId)->pluck('sub_category_id');
            if ($providerAddresses->count() > 0 && count($providerSubCategories)) {
                $postJobIds = PostJobRequest::whereHas('postCategoryMapping', function ($query) use ($providerSubCategories) {
                    $query->whereIn('sub_category_id', $providerSubCategories);
                })
                    ->where(function ($query) use ($providerAddresses) {
                        foreach ($providerAddresses as $address) {
                            $latitude = $address->latitude;
                            $longitude = $address->longitude;

                            $query->orWhereRaw("(
                                6371 * acos(
                                    cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) +
                                    sin(radians(?)) * sin(radians(latitude))
                                )
                            ) <= ?", [$latitude, $longitude, $latitude, 30]);
                        }
                    })
                    ->pluck('id');

                if (count($postJobIds) > 0) {
                    $post_request = $post_request->where('created_at', '>=', $user->created_at)->whereIn('post_job_requests.id', $postJobIds);
                } else {
                    $response = [
                        'pagination' => [
                            'total_items' => 0,
                            'per_page' => 0,
                            'currentPage' => 0,
                            'totalPages' => 0,
                            'from' => 0,
                            'to' => 0,
                            'next_page' => null,
                            'previous_page' => null,
                        ],
                        'data' => [],
                    ];

                    return comman_custom_response($response);
                }
            }
        }

        $post_request = $post_request->whereIn('post_job_requests.status', ['requested', 'accepted', 'assigned']);
//        $per_page = config('constant.PER_PAGE_LIMIT');

        $orderBy = $request->orderby ? $request->orderby: 'desc';

//        if($request->has('per_page') && !empty($request->per_page)){
//            if(is_numeric($request->per_page)){
//                $per_page = $request->per_page;
//            }
//            if($request->per_page === 'all' ){
//                $per_page = $post_request->count();
//            }
//        }

        $per_page = $post_request->count();

        $post_request = $post_request->orderBy('post_job_requests.id',$orderBy)->paginate($per_page);

        $items = PostJobRequestResource::collection($post_request);
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

    public function getPostRequestDetail(Request $request){
        $apiLogData = [
            'api_request' => json_encode($request->all()),
            'api_response' => '',
            'api_name' => 'postjob-details'
        ];
        ApiLog::insert($apiLogData);
        $id = $request->post_request_id;

        $post_request = PostJobRequest::myPostJob()->find($id);
        if(empty($post_request)){
            $message = __('messages.record_not_found');
            return comman_message_response($message, 400);
        }
        $post_request_detail = new PostJobRequestDetailResource($post_request);
        $bider_data = PostJobBiderResource::collection(PostJobBid::with(['provider', 'provider.providerSlots'])->where('post_request_id', $id)->get());
        $booking_data = Booking::where('post_request_id',$id)->get();

        $booking_address = "";

        if(!empty($booking_data) && isset($booking_data[0])){
            $booking_address = $booking_data[0]['address'];
        }

        $response = [
            'post_request_detail'    => $post_request_detail,
            'bider_data'    => $bider_data,
            'booking_address'    => $booking_address,
        ];

        return comman_custom_response($response);
    }

    public function getAudioServiceRequestList(Request $request)
    {
        $post_audio_request = AudioServiceRequest::whereNotNull('status');
        $per_page = config('constant.PER_PAGE_LIMIT');

        $orderBy = $request->orderby ? $request->orderby: 'desc';

        if($request->has('status') && !empty($request->status)){
            $post_audio_request->where('status',$request->status);
        }
        if($request->has('user_id') && !empty($request->user_id)){
            $post_audio_request->where('user_id',$request->user_id);
        }
        if($request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $post_audio_request->count();
            }
        }

        $post_audio_request = $post_audio_request->orderBy('id',$orderBy)->paginate($per_page);
        $items = AudioServiceRequestResource::collection($post_audio_request);
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
            'data' => $items
        ];

        return comman_custom_response($response);
    }
}
