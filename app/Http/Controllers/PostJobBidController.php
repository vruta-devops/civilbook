<?php

namespace App\Http\Controllers;

use App\Models\PostJobBid;
use App\Models\PostJobRequest;
use App\Models\User;
use Illuminate\Http\Request;

class PostJobBidController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $postJobRequest = PostJobRequest::where('id', $data['post_request_id'])->first();

        $data['customer_id'] = $postJobRequest->customer_id;

        $result = PostJobBid::updateOrCreate(['id' => $request->id], $data);

        $user = User::getUserByKeyValue('id', $postJobRequest->customer_id);

        if (!empty($user)) {
            $type = "place_bid";
            $providerName = auth()->user()->first_name;

            $notification_data = [
                'id' => $postJobRequest->id,
                'type' => $type,
                'subject' => $postJobRequest->title,
                'message' => "$providerName has been placed a bid for " . $postJobRequest->title,
            ];
            notificationSend($user, $type, $notification_data);
        }

        if ($request->has('attachments_count')) {
            $file = [];
            for ($i = 0; $i < $request->attachments_count; $i++) {
                $attachment = "bid_attachment_" . $i;
                if (!empty($request[$attachment])) {
                    $file[] = $request[$attachment];
                }
            }
            storeMediaFile($result, $file, 'post_job_bids');
        }

        $activity_data = [
            'activity_type' => 'provider_send_bid',
            'bid_data' => $result,
        ];

        saveJobActivity($activity_data);
        $message = __('messages.update_form',[ 'form' => __('messages.postbid') ] );
		if($result->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.postbid') ] );
		}

        if($request->is('api/*')) {
            return comman_message_response($message);
		}

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }
}
