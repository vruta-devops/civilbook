<?php

namespace App\Http\Controllers;

use App\DataTables\AudioServiceDataTable;
use App\DataTables\ServiceDataTable;
use App\DataTables\UserServiceDataTable;
use App\Http\Requests\ServiceRequest;
use App\Models\AudioServiceRequest;
use App\Models\PackageServiceMapping;
use App\Models\PostJobRequest;
use App\Models\PostJobServiceMapping;
use App\Models\Service;
use App\Models\ServiceSlotMapping;
use App\Models\Slider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
//    public function index(ServiceDataTable $dataTable, Request $request)
//    {
//        $pageTitle = __('messages.list_form_title',['form' => __('messages.service')] );
//        $auth_user = authSession();
//        $assets = ['datatable'];
//        return $dataTable->with([
//            'packageid' => $request->packageid,
//            'postjobid' => $request->postjobid
//        ])->render('service.index', compact('pageTitle', 'auth_user', 'assets'));
//        }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $id = null, $type = null)
    {
        if (!empty($id) && empty($type))
            session()->put('post_job_request', $id);

        if (!empty($type))
            session()->put('service_package_id', $id);

        $filter = [
            'status' => $request->status,
        ];
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.service')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('service.index', compact('pageTitle', 'auth_user', 'assets', 'filter'));
    }

    // get datatable data
    public function index_data(DataTables $datatable, Request $request)
    {
        $postJobRequestId = session()->get('post_job_request');
        $servicePackageId = session()->get('service_package_id');
        $query = Service::query()->myService();

        if (!empty($postJobRequestId) && empty($servicePackageId)) {
            $serviceId = PostJobServiceMapping::where('post_request_id', $postJobRequestId)->select('service_id')->first();
            if (!empty($serviceId))
                $query = Service::query()->myService()->where('id', $serviceId->service_id);

            session()->remove('post_job_request');
        } else if (!empty($servicePackageId)) {
            $serviceId = PackageServiceMapping::where('service_package_id', $servicePackageId)->select('service_id')->first();

            if (!empty($serviceId))
                $query = Service::query()->myService()->where('id', $serviceId->service_id);

            session()->remove('service_package_id');
        } else {
            $query = Service::query()->myService()->whereHas('addedBy', function ($query) {
                $query->where('user_type', '!=', 'user');
            });
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin']) && !empty($query)) {
            $query = $query->where('service_type', 'service')->withTrashed();
        }
        $query = $query->where('user_service_status', '!=', 0)->orderBy('id', 'desc');
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {

                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" data-type="service" onclick="dataTableRowCheck(' . $row->id . ',this)">';
            })
            ->editColumn('id', function ($query) {
                return "# " . $query->id;
            })
            ->editColumn('name', function ($query) {
                if (auth()->user()->can('service edit')) {
                    $link = '<a class="btn-link btn-link-hover" href=' . route('service.create', ['id' => $query->id]) . '>' . $query->name . '</a>';
                } else {
                    $link = $query->name;
                }
                return $link;
            })
            ->editColumn('area', function ($query) {
                return !empty($query->area) ? $query->area : "-";
            })
            ->editColumn('department', function ($query) {
                return !empty($query->department) ? $query->department->name : "-";
            })
            ->editColumn('category_id', function ($query) {
                return ($query->category_id != null && isset($query->category)) ? $query->category->name : '-';
            })
            ->editColumn('subcategory_id', function ($query) {
                if ($query->subcategory_id == 0) {
                    return 'All';
                }
                return ($query->subcategory_id != null && isset($query->subcategory)) ? $query->subcategory->name : '-';
            })
            ->filterColumn('category_id', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            // ->editColumn('provider_id' , function ($query){
            //     return ($query->provider_id != null && isset($query->providers)) ? $query->providers->display_name : '';
            // })
            ->editColumn('provider_id', function ($query) {
                return view('service.service', compact('query'));
            })
            ->filterColumn('provider_id', function ($query, $keyword) {
                $query->whereHas('providers', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('price', function ($query) {
                return getPriceFormat($query->price) . '-' . ucFirst($query->type);
            })
            ->editColumn('end_date', function ($query) {
                return empty($query->end_date) ? "-" : $query->end_date;
            })
            ->editColumn('end_date', function ($query) {
                return empty($query->end_date) ? "-" : $query->end_date;
            })
            ->editColumn('discount', function ($query) {
                return $query->discount ? $query->discount . '%' : '-';
            })
            ->addColumn('action', function ($data) {
                return view('service.action', compact('data'));
            })
            ->editColumn('user_service_status', function ($service) {
                $auth_user = authSession();
                if ($auth_user->user_type == 'admin') {
                    $disabled = $service->deleted_at ? 'disabled' : '';
                    return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                            <div class="custom-switch-inner">
                                <input type="checkbox" class="custom-control-input change_status" ' . $disabled . ' data-type="user_service_status" ' . (($service->user_service_status == 1) ? "checked" : "") . ' value="' . $service->id . '" id="userstatus' . $service->id . '" data-id="' . $service->id . '" >
                                <label class="custom-control-label" for="userstatus' . $service->id . '" data-on-label="" data-off-label=""></label>
                            </div>
                            </div>';
                } else {
                    if ($service->user_service_status == 1) {
                        $userServiceStatus = '<span class="badge badge-success">' . __('messages.approved') . '</span>';
                    } else {
                        $userServiceStatus = '<span class="badge badge-warning">' . __('messages.waiting_approval') . '</span>';
                    }
                    return $userServiceStatus;
                }
            })
            ->editColumn('status', function ($query) {
                $disabled = $query->trashed() || ($query->admin_service_type == 'common' && !auth()->user()->hasRole('admin')) ? 'disabled' : '';
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input  change_status" data-type="service_status" ' . ($query->status ? "checked" : "") . '  ' . $disabled . ' value="' . $query->id . '" id="' . $query->id . '" data-id="' . $query->id . '">
                        <label class="custom-control-label" for="' . $query->id . '" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->rawColumns(['action', 'status', 'check', 'name', 'user_service_status'])
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function pendingList(Request $request, $id = null, $type = null)
    {
        if (!empty($id) && empty($type))
            session()->put('post_job_request', $id);

        if (!empty($type))
            session()->put('service_package_id', $id);

        $filter = [
            'status' => $request->status,
        ];

        $pageTitle = __('messages.pending_list_form_title', ['form' => __('messages.service')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return view('service.pending-service', compact('pageTitle', 'auth_user', 'assets', 'filter'));
    }

    // get datatable data
    public function pendingListData(DataTables $datatable, Request $request)
    {
        $postJobRequestId = session()->get('post_job_request');
        $servicePackageId = session()->get('service_package_id');
        $query = Service::query()->myService();

        if (!empty($postJobRequestId) && empty($servicePackageId)) {
            $serviceId = PostJobServiceMapping::where('post_request_id', $postJobRequestId)->select('service_id')->first();
            if (!empty($serviceId))
                $query = Service::query()->myService()->where('id', $serviceId->service_id);

            session()->remove('post_job_request');
        } else if (!empty($servicePackageId)) {
            $serviceId = PackageServiceMapping::where('service_package_id', $servicePackageId)->select('service_id')->first();

            if (!empty($serviceId))
                $query = Service::query()->myService()->where('id', $serviceId->service_id);

            session()->remove('service_package_id');
        } else {
            $query = Service::query()->myService();
        }

        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin']) && !empty($query)) {
            $query = $query->where('service_type', 'service')->withTrashed();
        }
        $query = $query->where('user_service_status', 0)->orderBy('id', 'desc');
        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {

                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" data-type="service" onclick="dataTableRowCheck(' . $row->id . ',this)">';
            })
            ->editColumn('id', function ($query) {
                return "# " . $query->id;
            })
            ->editColumn('name', function ($query) {
                if (auth()->user()->can('service edit')) {
                    $link = '<a class="btn-link btn-link-hover" href=' . route('service.create', ['id' => $query->id]) . '>' . $query->name . '</a>';
                } else {
                    $link = $query->name;
                }
                return $link;
            })
            ->editColumn('area', function ($query) {
                return !empty($query->area) ? $query->area : "-";
            })
            ->editColumn('department', function ($query) {
                return !empty($query->department) ? $query->department->name : "-";
            })
            ->editColumn('category_id', function ($query) {
                return ($query->category_id != null && isset($query->category)) ? $query->category->name : '-';
            })
            ->editColumn('subcategory_id', function ($query) {
                if ($query->subcategory_id == 0) {
                    return 'All';
                }
                return ($query->subcategory_id != null && isset($query->subcategory)) ? $query->subcategory->name : '-';
            })
            ->filterColumn('category_id', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            // ->editColumn('provider_id' , function ($query){
            //     return ($query->provider_id != null && isset($query->providers)) ? $query->providers->display_name : '';
            // })
            ->editColumn('provider_id', function ($query) {
                return view('service.service', compact('query'));
            })
            ->filterColumn('provider_id', function ($query, $keyword) {
                $query->whereHas('providers', function ($q) use ($keyword) {
                    $q->where('display_name', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('price', function ($query) {
                return getPriceFormat($query->price) . '-' . ucFirst($query->type);
            })
            ->editColumn('end_date', function ($query) {
                return empty($query->end_date) ? "-" : $query->end_date;
            })
            ->editColumn('end_date', function ($query) {
                return empty($query->end_date) ? "-" : $query->end_date;
            })
            ->editColumn('discount', function ($query) {
                return $query->discount ? $query->discount . '%' : '-';
            })
            ->addColumn('action', function ($data) {
                $status = 'pending';
                return view('service.action', compact('data', 'status'));
            })
            ->editColumn('user_service_status', function ($service) {
                $auth_user = authSession();
                if ($auth_user->user_type == 'admin') {
                    $disabled = $service->deleted_at ? 'disabled' : '';
                    return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                            <div class="custom-switch-inner">
                                <input type="checkbox" class="custom-control-input change_status" ' . $disabled . ' data-type="user_service_status" ' . (($service->user_service_status == 1) ? "checked" : "") . ' value="' . $service->id . '" id="userstatus' . $service->id . '" data-id="' . $service->id . '" >
                                <label class="custom-control-label" for="userstatus' . $service->id . '" data-on-label="" data-off-label=""></label>
                            </div>
                            </div>';
                } else {
                    if ($service->user_service_status == 1) {
                        $userServiceStatus = '<span class="badge badge-success">' . __('messages.approved') . '</span>';
                    } else {
                        $userServiceStatus = '<span class="badge badge-warning">' . __('messages.waiting_approval') . '</span>';
                    }
                    return $userServiceStatus;
                }
            })
            ->editColumn('status', function ($query) {
                $disabled = $query->trashed() || ($query->admin_service_type == 'common' && !auth()->user()->hasRole('admin')) ? 'disabled' : '';
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input  change_status" data-type="service_status" ' . ($query->status ? "checked" : "") . '  ' . $disabled . ' value="' . $query->id . '" id="' . $query->id . '" data-id="' . $query->id . '">
                        <label class="custom-control-label" for="' . $query->id . '" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->rawColumns(['action', 'status', 'check', 'name', 'user_service_status'])
            ->toJson();
    }


    public function bulk_action(Request $request)
    {
        $ids = explode(',', $request->rowIds);

        $actionType = $request->action_type;

        $message = 'Bulk Action Updated';


        switch ($actionType) {
            case 'change-status':
                $branches = Service::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Service Status Updated';
                break;

            case 'delete':
                Service::whereIn('id', $ids)->delete();
                $message = 'Bulk Service Deleted';
                break;

            case 'restore':
                Service::whereIn('id', $ids)->restore();
                $message = 'Bulk Service Restored';
                break;

            case 'permanently-delete':
                Service::whereIn('id', $ids)->forceDelete();
                $message = 'Bulk Service Permanently Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => 'Bulk Action Updated']);
    }

    /* user service list */

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /* audio service list */

    public function getUserServiceList(UserServiceDataTable $dataTable, Request $request)
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.service')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('service.user_service_list', compact('pageTitle', 'auth_user', 'assets'));
    }

    public function getAudioServiceList(AudioServiceDataTable $dataTable, Request $request)
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.post_job_audio_service')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('service.audio_service_list', compact('pageTitle', 'auth_user', 'assets'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ServiceRequest $request)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }

        $services = $request->all();
        $slotData = [];

        if (!empty($services['slots'])) {
            $slotData = $services['slots'];
            unset($services['slots']);
        }

        if (empty($services['type_id'])) {
            $services['type_id'] = null;
        }

        if (empty($services['price_type_id'])) {
            $services['price_type_id'] = null;
        }
        if (empty($services['price'])) {
            $services['price'] = 0;
        }
        if (empty($services['shift_type_id'])) {
            $services['shift_type_id'] = null;
        }

        if (empty($services['shift_hour_id'])) {
            $services['shift_hour_id'] = null;
        }

        if (empty($services['material_unit_id'])) {
            $services['material_unit_id'] = null;
        }

        if (empty($services['qualification'])) {
            $services['qualification'] = null;
        }

        $services['service_type'] = !empty($request->service_type) ? $request->service_type : 'service';
        $services['provider_id'] = !empty($request->provider_id) ? $request->provider_id : auth()->user()->id;
        if (auth()->user()->hasRole('user')) {
            $services['service_type'] = 'user_post_service';
        }

        if ($request->id == null && default_earning_type() === 'subscription') {
            $exceed = get_provider_plan_limit($services['provider_id'], 'service');
            if (!empty($exceed)) {
                if ($exceed == 1) {
                    $message = __('messages.limit_exceed', ['name' => __('messages.service')]);
                } else {
                    $message = __('messages.not_in_plan', ['name' => __('messages.service')]);
                }
                if ($request->is('api/*')) {
                    return comman_message_response($message);
                } else {
                    return redirect()->back()->withErrors($message);
                }
            }
        }

        if ($request->id == null) {
            $services['added_by'] = !empty($request->added_by) ? $request->added_by : auth()->user()->id;
        }

        $services['provider_id'] = !empty($services['provider_id']) ? $services['provider_id'] : auth()->user()->id;
        if (!empty($services['is_featured']) && $services['is_featured'] == 1) {
            $exceed = get_provider_plan_limit($services['provider_id'], 'featured_service');
            if (!empty($exceed)) {
                if ($exceed == 1) {
                    $message = __('messages.limit_exceed', ['name' => __('messages.featured_service')]);
                } else {
                    $message = __('messages.not_in_plan', ['name' => __('messages.featured_service')]);
                }
                if ($request->is('api/*')) {
                    return comman_message_response($message);
                } else {
                    return redirect()->back()->withErrors($message);
                }
            }
        }

        if (!$request->is('api/*')) {
            $services['is_featured'] = 0;
            $services['is_slot'] = 0;
            //$services['digital_service'] = 0;
            $services['is_enable_advance_payment'] = 0;

            if ($request->has('is_featured')) {
                $services['is_featured'] = 1;
            }
            if ($request->has('is_enable_advance_payment')) {
                $services['is_enable_advance_payment'] = 1;
            }
            if ($request->has('is_slot')) {
                $services['is_slot'] = 1;
            }
            // if($request->has('digital_service')){
            //     $services['digital_service'] = 1;
            // }
        }

        if (!$request->is('api/*')) {
            $services['provider_id'] = (!empty($request->provider_id)) ? $request->provider_id[0] : auth()->user()->id;
            //$provider_id = explode(',', $request->provider_id);
        } else {
            $services['provider_id'] = $request->provider_id > 0 ? $request->provider_id : auth()->user()->id;
        }

        if (!empty($request->advance_payment_amount)) {
            $services['advance_payment_amount'] = floatval($request->advance_payment_amount);
        }

        $services['start_date'] = !empty($request->start_date) ? date('Y-m-d', strtotime($request->start_date)) : date('Y-m-d');
        $services['end_date'] = !empty($request->end_date) ? date('Y-m-d', strtotime($request->end_date)) : date('Y-m-d', strtotime(date('Y-m-d') . ' +2 years'));

        if (!empty($request->id)) {
            $serviceObj = Service::with(['addedBy'])->where('id', $request->id)->first();
            $services['user_service_status'] = !empty($request->user_service_status) ? $request->user_service_status : 0;

            if (!empty($serviceObj) && $serviceObj->addedBy->user_type === 'user') {
                $services['user_service_status'] = 1;
            }
        }

        $services['admin_service_type'] = !empty($request->admin_service_type) ? $request->admin_service_type : 'self';

        $services['with_transport'] = empty($request->with_transport) ? null : $request->with_transport;

        $result = Service::updateOrCreate(['id' => $request->id], $services);

        if (empty($request->id) && getLoggedUserType() === 'provider') {
            $user = auth()->user();
            $notificationData = [
                'id' => $result->id,
                'type' => 'new_service_create',
                'subject' => 'new_service_create',
                'message' => $user->first_name . " has been create a new service. Please approve/reject their service",
            ];

            saveAdminNotification($notificationData);
        }

        if (!empty($slotData)) {
            $serviceId = $result->id;
            ServiceSlotMapping::where('service_id', $serviceId)->delete();

            $timeStamp = Carbon::now();

            $slotData = gettype($slotData) == 'string' ? json_decode($slotData, true) : $slotData;

            $slotArray = [];
            foreach ($slotData as $value) {
                if (!empty($value['slot'])) {

                    foreach ($value['slot'] as $time) {
                        $slotArray[] = [
                            'service_id' => $serviceId,
                            'day' => $value['day'],
                            'start_at' => $time,
                            'end_at' => date('H:i', strtotime('+1 hour', strtotime($time))),
                            'created_at' => $timeStamp,
                            'updated_at' => $timeStamp,
                        ];
                    }
                }
            }
            if (!empty($slotArray)) {
                ServiceSlotMapping::insert($slotArray);
            }
        }

        if ($result->providerServiceAddress()->count() > 0) {
            $result->providerServiceAddress()->delete();
        }

        if ($request->provider_address_id != null) {
            foreach ($request->provider_address_id as $address) {
                $provider_service_address = [
                    'service_id' => $result->id,
                    'provider_address_id' => $address,
                ];
                $result->providerServiceAddress()->insert($provider_service_address);
            }
        }

        if (isset($request->admin_service_type)) {
            if ($result->serviceProviderMapping()->count() > 0) {
                $result->serviceProviderMapping()->delete();
            }

            if ($request->is('api/*')) {
                $providers = User::where('user_type', 'provider')->where('status', 1)->where('id', $request->provider_id)->get();
            } else {
                $providers = User::where('user_type', 'provider')->where('status', 1);
                if ($request->admin_service_type == 'common' && empty($request->provider_id)) {
                    $providers = $providers->get();
                } else {
                    if (auth()->user()->user_type == 'provider') {
                        $providers = $providers->where('id', auth()->user()->id)->get();
                    } else {
                        $providers = $providers->where(function ($query) use ($request) {
                            if (gettype($request->provider_id) == 'string') {
                                $query = $query->where('id', explode(',', $request->provider_id));
                            } else {
                                foreach ($request->provider_id as $index => $providerId) {
                                    if ($index == 0)
                                        $query = $query->where('id', $providerId);
                                    else
                                        $query = $query->orWhere('id', $providerId);
                                }
                            }
                            return $query;
                        })->get();
                    }
                }
            }

            $type = 'new_service_create';

            $notification_data = [
                'id' => $result->id,
                'type' => $type,
                'subject' => $result->name,
                'message' => 'Admin has created new service',
            ];

            foreach ($providers as $key => $provider) {
                $service_provider = [
                    'service_id' => $result->id,
                    'provider_id' => $provider->id,
                ];

                $result->serviceProviderMapping()->create($service_provider);
                if (!empty($provider->player_id) && auth()->user()->user_type == 'admin') {
                    notificationSend($provider, $type, $notification_data);
                }
            }
        }

        if ($request->is('api/*')) {
            if ($request->has('attachment_count')) {
                $file = [];
                for ($i = 0; $i < $request->attachment_count; $i++) {
                    $attachment = "service_attachment_" . $i;
                    if ($request->$attachment != null) {
                        $file[] = $request->$attachment;
                    }
                }
                storeMediaFile($result, $file, 'service_attachment');
            }
        } elseif (isset($request->service_attachment)) {
            storeMediaFile($result, $request->service_attachment, 'service_attachment');
        }

        $message = __('messages.update_form', ['form' => __('messages.service')]);
        if ($result->wasRecentlyCreated) {
            $message = __('messages.save_form', ['form' => __('messages.service')]);
        }

        if ($request->is('api/*')) {
            $response = [
                'message' => $message,
                'service_id' => $result->id
            ];
            return comman_custom_response($response);
        }
        if (!empty($services['filter_status'])) {
            return redirect(route('service.pending-service'))->withSuccess($message);
        }
        return redirect(route('service.index'))->withSuccess($message);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;

        $auth_user = auth()->user();

        if ($auth_user->user_type == 'provider' && $auth_user->is_subscribe == 0) {
            return redirect(route('service.index'))->withErrors("You've no subscription plan to create service");
        }

        $servicedata = Service::with('providers', 'category', 'departmentType', 'shiftType', 'shiftHour', 'slots', 'materialUnit')->find($id);
        $visittype = config('constant.VISIT_TYPE');
        $preDiff = config('constant.PRE_DISTANCE');

        $pageTitle = __('messages.update_form_title', ['form' => __('messages.service')]);

        if ($servicedata == null) {
            $pageTitle = __('messages.add_button_form', ['form' => __('messages.service')]);
            $servicedata = new Service;
        }
        return view('service.create', compact('pageTitle', 'servicedata', 'auth_user', 'visittype', 'preDiff'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceDataTable $dataTable, Request $request, $id)
    {
        $auth_user = authSession();
        $tabpage = 'all-plan';
        $providerdata = User::with('providerDocument')->where('user_type', 'provider')->where('id', $id)->first();
        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);

        return view('service.view', compact('pageTitle', 'providerdata', 'auth_user', 'tabpage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            if (demoUserPermission()) {
                if (request()->is('api/*')) {
                    return comman_message_response(__('messages.demo_permission_denied'));
                }
                return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
            }
            $service = Service::find($id);
            $msg = __('messages.msg_fail_to_delete', ['item' => __('messages.service')]);

            if ($service != '') {
                $service->delete();
                Slider::where('type_id', $id)->delete();
                $msg = __('messages.msg_deleted', ['name' => __('messages.service')]);
            }
            DB::commit();
            if (request()->is('api/*')) {
                return comman_custom_response(['message' => $msg, 'status' => true]);
            }
            return comman_custom_response(['message' => $msg, 'status' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
        }
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $service = Service::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('messages.service')]);
        if ($request->type === 'restore') {
            $service->restore();
            $msg = __('messages.msg_restored', ['name' => __('messages.service')]);
        }

        if ($request->type === 'forcedelete') {
            $service->forceDelete();
            $msg = __('messages.msg_forcedelete', ['name' => __('messages.service')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function audioServiceDestroy($id)
    {
        if (demoUserPermission()) {
            if (request()->is('api/*')) {
                return comman_message_response(__('messages.demo_permission_denied'));
            }
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $audio_service = AudioServiceRequest::find($id);
        $msg = __('messages.msg_fail_to_delete', ['item' => __('messages.audio_service')]);

        if ($audio_service != '') {
            $audio_service->update(['status' => 3]);
            $audio_service->delete();

            $msg = __('messages.msg_deleted', ['name' => __('messages.audio_service')]);
        } else {
            $msg = __('messages.record_not_found');
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function audioServicePost($id, Request $request)
    {

        $audioservicedata = AudioServiceRequest::find($id);
        $pageTitle = __('messages.update_form_title', ['form' => __('messages.audio_service')]);
        return view('service.audio_service_post_request', compact('audioservicedata', 'pageTitle'));
    }


    public function audioServicePostSave(Request $request)
    {

        $audio_service = AudioServiceRequest::find($request->id);
        if ($request->status == 1) {
            if ($audio_service != '') {
                $audio_service->update(['status' => 3]);
                $audio_service->delete();
            }
            $catName = "";
            if (isset($request->category_id[0])) {
                $catName = $request->category_id[0];
            }
            $services['service_type'] = 'user_post_service';
            $services['name'] = $request->title;
            $services['description'] = $request->description;
            $services['type'] = 'service';
            $services['price'] = $request->price;
            $services['category_id'] = $catName;
            $services['status'] = 1;
            $services['duration'] = 0;
            $services['added_by'] = auth()->user()->id;
            $services['provider_id'] = auth()->user()->id;
            $services['admin_service_type'] = 'self';

            $resultService = Service::create($services);

            $data['customer_id'] = !empty($request->user_id) ? $request->user_id : auth()->user()->id;
            $data['title'] = $request->title;
            $data['description'] = $request->description;
            $data['price'] = $request->price;
            $data['status'] = 'requested';
            $result = PostJobRequest::create($data);

            $activity_data = [
                'activity_type' => 'job_requested',
                'post_job_id' => $result->id,
                'post_job' => $result,
            ];

            saveRequestJobActivity($activity_data);

            if ($result->postServiceMapping()->count() > 0) {
                $result->postServiceMapping()->delete();
            }
            if ($resultService->id != null) {
                $resultServiceId = explode(",", $resultService->id);
                foreach ($resultServiceId as $service) {
                    $post_services = [
                        'post_request_id' => $result->id,
                        'service_id' => $service,
                    ];
                    $result->postServiceMapping()->insert($post_services);
                }
            }

            if ($request->category_id != 0) {
                if ($result->postCategoryMapping()->count() > 0) {
                    $result->postCategoryMapping()->delete();
                }

                foreach ($request->category_id as $key => $category) {
                    if ($request->sub_category_id != 0) {
                        foreach ($request->sub_category_id as $sub_category) {
                            $post_category = [
                                'post_request_id' => $result->id,
                                'category_id' => $category,
                                'sub_category_id' => $sub_category,
                            ];
                            $result->postCategoryMapping()->insert($post_category);
                        }
                    } else {
                        $post_category = [
                            'post_request_id' => $result->id,
                            'category_id' => $category,
                            'sub_category_id' => isset($request->sub_category_id[$key]) ? $request->sub_category_id[$key] : 0,
                        ];
                        $result->postCategoryMapping()->insert($post_category);
                    }
                }
            }

            if ($result->wasRecentlyCreated) {
                $message = __('messages.save_form', ['form' => __('messages.postrequest')]);
            }

            // if ($request->status == 'accept') {
            //     $activity_data = [
            //         'activity_type' => 'user_accept_bid',
            //         'post_job_id' => $result->id,
            //         'post_job' => $result,
            //     ];

            //     saveRequestJobActivity($activity_data);
            // }
        } elseif ($request->status == 2) {
            if ($audio_service != '') {
                $audio_service->update(['status' => 2]);
                $message = __('messages.reject_form', ['form' => __('messages.post_job_audio_service')]);
            }
        } else {
            if ($audio_service != '') {
                $audio_service->update(['status' => 0]);
            }
            $message = __('messages.update_form', ['form' => __('messages.post_job_audio_service')]);
        }
        return redirect(route('service.audio-service-list'))->withSuccess($message);
    }

    public function audioServiceAction(Request $request)
    {
        $id = $request->id;
        $audio_service = AudioServiceRequest::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('messages.audio_service')]);
        if ($request->type === 'restore') {
            $audio_service->update(['status' => 0]);
            $audio_service->restore();
            $msg = __('messages.msg_restored', ['name' => __('messages.audio_service')]);
        }

        if ($request->type === 'forcedelete') {
            $audio_service->forceDelete();
            $msg = __('messages.msg_forcedelete', ['name' => __('messages.audio_service')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }
}
