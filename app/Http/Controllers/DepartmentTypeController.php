<?php

namespace App\Http\Controllers;

use App\DataTables\DepartmentTypeDataTable;
use App\Http\Requests\BookingUpdateRequest;
use App\Models\Booking;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Type;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class DepartmentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DepartmentTypeDataTable $dataTable)
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.department_type')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('departmentType.index', compact('pageTitle', 'auth_user', 'assets'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $query = Type::query();

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" data-type="department" onclick="dataTableRowCheck(' . $row->id . ',this)">';
            })
            ->editColumn('name', function ($query) {
                return $query->name;
            })
            ->addColumn('action', function ($departmentType) {
                return view('departmentType.action', compact('departmentType'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'status', 'name', 'check'])
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
                $branches = Booking::whereIn('id', $ids)->update(['status' => $request->status]);
                $message = 'Bulk Booking Status Updated';
                break;

            case 'delete':
                Booking::whereIn('id', $ids)->delete();
                $message = 'Bulk Booking Deleted';
                break;

            case 'restore':
                Booking::whereIn('id', $ids)->restore();
                $message = 'Bulk Booking Restored';
                break;

            case 'permanently-delete':
                Booking::whereIn('id', $ids)->forceDelete();
                $message = 'Bulk Booking Permanently Deleted';
                break;

            default:
                return response()->json(['status' => false, 'message' => 'Action Invalid']);
                break;
        }

        return response()->json(['status' => true, 'message' => 'Bulk Action Updated']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(BookingUpdateRequest $request, $id)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $data = $request->all();


        $data['date'] = isset($request->date) ? date('Y-m-d H:i:s', strtotime($request->date)) : date('Y-m-d H:i:s');
        $data['start_at'] = isset($request->start_at) ? date('Y-m-d H:i:s', strtotime($request->start_at)) : null;
        $data['end_at'] = isset($request->end_at) ? date('Y-m-d H:i:s', strtotime($request->end_at)) : null;


        $departmentData = Booking::find($id);
        $auth_user = authSession();
        if ($request->is('api/*') && $departmentData->status != 'pending' && $departmentData->provider_id != $auth_user->id) {
            $msg = __('messages.already_in_status', ['status' => $data['status']]);
            return redirect()->back()->withSuccess($msg);
        }
        $data['provider_id'] = $departmentData->provider_id;
        if ($auth_user->type == 'provider' && $departmentData->provider_id != $auth_user->id) {
            $data['provider_id'] = $auth_user->id;
        }
        $paymentdata = Payment::where('booking_id', $id)->first();
        if ($data['status'] === 'hold') {
            if ($departmentData->start_at == null && $departmentData->end_at == null) {
                $duration_diff = duration($data['start_at'], $data['end_at'], 'in_minute');
                $data['duration_diff'] = $duration_diff;
            } else {
                if ($departmentData->status == $data['status']) {
                    $booking_start_date = $departmentData->start_at;
                    $request_start_date = $data['start_at'];
                    if ($request_start_date > $booking_start_date) {
                        $msg = __('messages.already_in_status', ['status' => $data['status']]);
                        return redirect()->back()->withSuccess($msg);
                    }
                } else {
                    $duration_diff = $departmentData->duration_diff;
                    $new_diff = duration($departmentData->start_at, $departmentData->end_at, 'in_minute');
                    $data['duration_diff'] = $duration_diff + $new_diff;
                }
            }
        }
        if ($departmentData->status != $data['status']) {
            $activity_type = 'update_booking_status';
        }
        if ($data['status'] == 'cancelled') {
            $activity_type = 'cancel_booking';
        }
        $data['reason'] = isset($data['reason']) ? $data['reason'] : null;
        $old_status = $departmentData->status;

        $assign_handyman_id = [];
        if (!$request->is('api/*') && isset($request->provider_id) && $data['status'] == 'accept') {
            if ($request->handyman_id == null) {
                $assign_handyman_id = $request->provider_id;
            } else {
                $assign_handyman_id = $request->handyman_id;
            }
            $data['provider_id'] = $request->provider_id[0];
        } elseif (!$request->is('api/*') && isset($request->assignto) && $request->assignto == 'myself' && $data['status'] == 'accept') {
            $assign_handyman_id = ($auth_user->user_type == 'provider') ? [$auth_user->id] : [];
            $data['provider_id'] = $auth_user->id;
        } elseif (isset($request->handyman_id) && $request->handyman_id != null) {
            $assign_handyman_id = $request->handyman_id;
        }
        $departmentData->update($data);

        $assigned_handyman_ids = [];
        $remove_notification_id = [];
        if (count($assign_handyman_id) > 0) {

            if ($departmentData->handymanAdded()->count() > 0) {
                $assigned_handyman_ids = $departmentData->handymanAdded()->pluck('handyman_id')->toArray();
                $departmentData->handymanAdded()->delete();
                $activity_type = 'transfer_booking';
            } else {
                $activity_type = 'assigned_booking';
            }

            foreach ($assign_handyman_id as $handyman) {
                $assign_to_handyman = [
                    'booking_id' => $departmentData->id,
                    'handyman_id' => $handyman
                ];
                $remove_notification_id = removeArrayValue($assigned_handyman_ids, $handyman);
                $departmentData->handymanAdded()->insert($assign_to_handyman);
            }
        }

        if (!empty($remove_notification_id)) {

            Notification::whereIn('notifiable_id', $remove_notification_id)
                ->whereJsonContains('data->id', $departmentData->id)
                ->delete();
        }

        if ($old_status != $data['status']) {
            $departmentData->old_status = $old_status;
            $activity_data = [
                'activity_type' => $activity_type,
                'booking_id' => $id,
                'booking' => $departmentData,
            ];

            saveBookingActivity($activity_data);
        }
        if ($departmentData->payment_id != null) {
            $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
            $paymentdata->update($data);

            if ($departmentData->payment_id != null) {
                $data['payment_status'] = isset($data['payment_status']) ? $data['payment_status'] : 'pending';
                $paymentdata->update($data);
                $activity_data = [
                    'activity_type' => 'payment_message_status',
                    'payment_status' => $data['payment_status'],
                    'booking_id' => $id,
                    'booking' => $departmentData,
                ];
                saveBookingActivity($activity_data);
            }
        }
        $message = __('messages.update_form', ['form' => __('messages.booking')]);

        if ($request->is('api/*')) {
            return comman_message_response($message);
        }

        return redirect(route('booking.index'))->withSuccess($message);
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

        $departmentTypeData = Type::find($id);
        $pageTitle = __('messages.update_form_title', ['form' => __('messages.department_type')]);

        if ($departmentTypeData == null) {
            $pageTitle = __('messages.add_button_form', ['form' => __('messages.department_type')]);
            $departmentTypeData = new Type();
        }
        return view('departmentType.create', compact('pageTitle', 'departmentTypeData'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $result = Type::updateOrCreate(['id' => $data['id']], $data);

        $message = __('messages.save_form', ['form' => __('messages.department_type')]);

        return redirect(route('department-types.index'))->withSuccess($message);

    }
}
