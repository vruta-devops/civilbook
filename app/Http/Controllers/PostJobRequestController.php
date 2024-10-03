<?php

namespace App\Http\Controllers;

use App\DataTables\PostJobBidDataTable;
use App\DataTables\PostjobRequestsDataTable;
use App\Models\AudioServiceRequest;
use App\Models\PostJobRequest;
use App\Models\ProviderAddressMapping;
use App\Models\ProviderCategoryMapping;
use App\Models\Service;
use App\Models\SubCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostJobRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PostjobRequestsDataTable $dataTable)
    {
        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.postjob')] );
        $auth_user = authSession();
        $assets = ['datatable'];

        return $dataTable->render('postrequest.index', compact('pageTitle','auth_user','assets'));

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
        DB::beginTransaction();
        try {
            $data = $request->all();
            //name => servicename, description, type "fixed", price=0, addedby=>userid,provider_id,category_id,status => 1, duration => 0
            if ($request->request_type == 'normal') {
                if ($request->id == null && $request->status != 'assigned') {
                    $data['customer_id'] = !empty($request->customer_id) ? $request->customer_id : auth()->user()->id;
                    $catId = "";
                    if ($request->is('api/*') && isset($request->category_id)) {
                        $catId = $request->category_id;
                    } else {
                        if (count($request->category_id) > 0) {
                            $catId = $request->category_id[0];
                        }
                    }
                    $services['department_id'] = $request->department_id;
                    $services['service_type'] = 'service';
                    $services['name'] = $request->title;
                    $services['description'] = $request->description;
                    $services['type'] = 'service';
                    $services['price'] = 0;
                    $services['category_id'] = $catId;
                    $services['status'] = 1;
                    $services['user_service_status'] = 1;
                    $services['duration'] = 0;

                    if ($request->id == null) {
                        $services['added_by'] = !empty($request->added_by) ? $request->added_by : auth()->user()->id;
                    }

                    $services['provider_id'] = auth()->user()->id;

                    if (!empty($request->advance_payment_amount)) {
                        $services['advance_payment_amount'] = $request->advance_payment_amount;
                    }
                    $services['subcategory_id'] = $data['sub_category_id'];
                    $services['is_post_job'] = 1;
                    $resultService = Service::updateOrCreate(['id' => $request->id], $services);
                }

                $data['is_all_sub_categories'] = false;
                if (isset($data['sub_category_id']) && $data['sub_category_id'] == 0) {
                    $data['is_all_sub_categories'] = true;
                }
                if (!empty($request->status) && $request->status === 'requested')
                    $data['expired_at'] = Carbon::now()->addDay(7);
                $result = PostJobRequest::updateOrCreate(['id' => $request->id], $data);

                if ($data['status'] === 'requested') {
                    $type = "new_post_job_create";
                    $senderUser = User::find($data['customer_id']);

                    $notification_data = [
                        'id' => $result->id,
                        'type' => $type,
                        'subject' => $data['title'],
                        'message' => $senderUser->first_name . " has been create new post job request " . $data['title'],
                    ];

                    $providersIds = ProviderCategoryMapping::where('category_id', $data['category_id']);

                    if (!empty($data['sub_category_id']) && $data['sub_category_id'] != 0) {
                        $providersIds = $providersIds->where('sub_category_id', $data['sub_category_id']);
                    }

                    $radius = 30;
                    $latitude = $request->latitude;
                    $longitude = $request->longitude;

                    $providersIds = $providersIds->pluck('provider_id');

                    $providersIds = ProviderAddressMapping::select('provider_id', DB::raw("(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))) AS distance"))
                        ->having('distance', '<=', $radius)
                        ->orderBy('distance')
                        ->whereIn('provider_id', $providersIds)
                        ->pluck('provider_id');

                    $users = User::whereIn('id', $providersIds)->where('is_subscribe', 1)->get();

                    foreach ($users as $user) {
                        if (!empty($user->player_id)) {
                            notificationSend($user, $type, $notification_data);
                        }
                    }
                }

                if ($request->has('attachments_count')) {
                    $file = [];
                    for ($i = 0; $i < $request->attachments_count; $i++) {
                        $attachment = "post_job_attachment_" . $i;
                        if (!empty($request[$attachment])) {
                            $file[] = $request[$attachment];
                        }
                    }
                    storeMediaFile($result, $file, 'post_job_attachment');
                }

                $activity_data = [
                    'activity_type' => 'job_requested',
                    'post_job_id' => $result->id,
                    'post_job' => $result,
                ];

                saveRequestJobActivity($activity_data);

                if ($request->status != 'assigned') {
                    if ($result->postServiceMapping()->count() > 0) {
                        $result->postServiceMapping()->delete();
                    }
                }

                if ($request->id == null) {
                    if ($resultService->id != null) {
                        if (gettype($resultService->id) === 'integer') {
                            $post_services = [
                                'post_request_id' => $result->id,
                                'service_id' => $resultService->id,
                            ];
                            $result->postServiceMapping()->insert($post_services);
                        } else {
                            $resultServiceId = explode(",", $resultService->id);
                            foreach ($resultServiceId as $service) {
                                $post_services = [
                                    'post_request_id' => $result->id,
                                    'service_id' => $service,
                                ];
                                $result->postServiceMapping()->insert($post_services);
                            }
                        }

                    }
                }

                if ($request->category_id != null) {
                    if ($result->postCategoryMapping()->count() > 0) {
                        $result->postCategoryMapping()->delete();
                    }

                    if ($request->is('api/*')) {
                        if (isset($request->sub_category_id)) {
                            $subCategories = SubCategory::where('status', 1)->where('category_id', $request->category_id);
                            if (!empty($request->sub_category_id) && $request->sub_category_id > 0) {
                                $subCategories = $subCategories->where('id', $request->sub_category_id);
                            }
                            $subCategories = $subCategories->select('id', 'category_id')->get();
                            foreach ($subCategories as $subCategory) {
                                $post_category = [
                                    'post_request_id' => $result->id,
                                    'category_id' => $subCategory->category_id,
                                    'sub_category_id' => $subCategory->id,
                                ];
                                $result->postCategoryMapping()->create($post_category);
                            }
                        }
                    }
                }
                if ($request->status == 'accept') {
                    $activity_data = [
                        'activity_type' => 'user_accept_bid',
                        'post_job_id' => $result->id,
                        'post_job' => $result,
                    ];

                    saveRequestJobActivity($activity_data);
                }

                $message = __('messages.update_form', ['form' => __('messages.postrequest')]);

                if ($result->wasRecentlyCreated) {
                    $message = __('messages.save_form', ['form' => __('messages.postrequest')]);
                }
            } elseif ($request->request_type == 'audio') {
                $audio_data = [
                    'user_id' => !empty($request->added_by) ? $request->added_by : auth()->user()->id,
                    'audio' => $request->audio,
                    'description' => !empty($request->description) ? $request->description : '',
                    'status' => !empty($request->status) ? $request->status : 0,
                ];
                $resultAudio = AudioServiceRequest::create($audio_data);
                storeMediaFile($resultAudio, $request->audio, 'audio');
                $message = __('messages.save_form', ['form' => __('messages.postrequestaudio')]);
            }
            DB::commit();
            if ($request->is('api/*')) {
                return comman_message_response($message);
            }

            return redirect(route('service.index'))->withSuccess($message);
        } catch (\Exception $e) {
            DB::rollBack();
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
    public function show($id, PostJobBidDataTable $dataTable)
    {
        $pageTitle = trans('messages.list_form_title',['form' => trans('messages.postbid')] );
        $auth_user = authSession();
        $asset = ['datatable'];
        return $dataTable->with('id', $id)->render('postrequest.view', compact('pageTitle', 'auth_user', 'asset'));
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
            if(request()->is('api/*')){
                return comman_message_response( __('messages.demo_permission_denied') );
            }
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $post_request = PostJobRequest::find($id);
        //$post_request->delete();
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.postrequest')] );

        if($post_request!='') {
            if($post_request->postServiceMapping()->count() > 0)
            {
                $post_request->postServiceMapping()->delete();
            }
            if($post_request->postBidList()->count() > 0)
            {
                $post_request->postBidList()->delete();
            }
            if($post_request->postBidList()->count() > 0)
            {
                $post_request->postBidList()->delete();
            }
            $post_request->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.postrequest')] );
        }
        if(request()->is('api/*')){
            return comman_custom_response(['message'=> $msg , 'status' => true]);
        }
        return redirect()->back()->withSuccess($msg);

    }
}
