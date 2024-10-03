<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\PostJobBiderResource;
use App\Models\ApiLog;
use App\Models\BidComment;
use App\Models\PostJobBid;
use App\Models\User;
use Illuminate\Http\Request;

class PostJobBidController extends Controller
{
    public function bidComments(Request $request)
    {
        $data = $request->all();

        $postJobBidRequest = PostJobBid::with(['postrequest'])->where('id', $data['post_job_bid_id'])->first();

        if (empty($postJobBidRequest)) {
            $message = __('messages.record_not_found');
            return comman_message_response($message, 400);
        }

        $apiLogData = [
            'api_request' => json_encode($data),
            'api_response' => '',
            'api_name' => '\API\bidComments'
        ];
        ApiLog::insert($apiLogData);

        $bidComment = [
            'post_job_bid_id' => $data['post_job_bid_id'],
            'comment' => $data['comment'],
            'sender_type' => $data['sender_type']
        ];

        $response = BidComment::create($bidComment);

        if ($data['sender_type'] == 'user') {
            $user = User::getUserByKeyValue('id', $postJobBidRequest->provider_id);
            $senderUser = User::getUserByKeyValue('id', $postJobBidRequest->customer_id);
        } else {
            $user = User::getUserByKeyValue('id', $postJobBidRequest->customer_id);
            $senderUser = User::getUserByKeyValue('id', $postJobBidRequest->provider_id);
        }

        if (!empty($user)) {
            $type = "bid_comment";

            $notification_data = [
                'id' => $postJobBidRequest->postrequest->id,
                'type' => $type,
                'subject' => $postJobBidRequest->postrequest->title,
                'message' => $senderUser->first_name . " has been added comment for " . $postJobBidRequest->postrequest->title,
            ];

            notificationSend($user, $type, $notification_data);
        }


        return comman_custom_response($response);
    }

    public function getPostBidList(Request $request){
        $data = $request->all();
        $apiLogData = [
            'api_request' => json_encode($data),
            'api_response' => '',
            'api_name' => '\API\getPostBidList'
        ];
        ApiLog::insert($apiLogData);

        $user = auth()->user();
        $post_request = PostJobBid::myPostJobBid();
        $per_page = config('constant.PER_PAGE_LIMIT');

        $orderBy = $request->orderby ? $request->orderby: 'asc';

        if( $request->has('per_page') && !empty($request->per_page)){
            if(is_numeric($request->per_page)){
                $per_page = $request->per_page;
            }
            if($request->per_page === 'all' ){
                $per_page = $post_request->count();
            }
        }

        $post_request = $post_request->orderBy('id',$orderBy)->paginate($per_page);
        $items = PostJobBiderResource::collection($post_request);

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
