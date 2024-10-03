<?php

namespace App\Http\Controllers;

use App\DataTables\DepartmentDataTable;
use App\Http\Requests\BookingUpdateRequest;
use App\Models\AppSetting;
use App\Models\Booking;
use App\Models\BookingStatus;
use App\Models\Certificate;
use App\Models\Department;
use App\Models\MaterialUnits;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PriceTypes;
use App\Models\ShiftType;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use PDF;
use Yajra\DataTables\DataTables;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(DepartmentDataTable $dataTable)
    {
        $pageTitle = __('messages.list_form_title', ['form' => __('messages.department')]);
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('department.index', compact('pageTitle', 'auth_user', 'assets'));
    }

    public function index_data(DataTables $datatable, Request $request)
    {
        $query = Department::query();
        $filter = $request->filter;

        if (isset($filter)) {
            if (isset($filter['column_status'])) {
                $query->where('status', $filter['column_status']);
            }
        }
        if (auth()->user()->hasAnyRole(['admin'])) {
            $query->withTrashed();
        }

        return $datatable->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="form-check-input select-table-row"  id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" data-type="department" onclick="dataTableRowCheck(' . $row->id . ',this)">';
            })
//            ->editColumn('id' , function ($query){
//                return "<a class='btn-link btn-link-hover' href=" .route('department.show', $query->id).">#".$query->id ."</a>";
//            })
            // ->editColumn('customer_id' , function ($query){
            //     return ($query->customer_id != null && isset($query->customer)) ? $query->customer->display_name : '';
            // })
            ->editColumn('name', function ($query) {
                return $query->name;
            })
            ->editColumn('status', function ($query) {
                if ($query->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                } else {
                    return '<span class="badge badge-danger">In Active</span>';
                }
            })
            ->addColumn('action', function ($department) {
                return view('department.action', compact('department'))->render();
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

        $departmentData = Department::with(['departmentShiftHoursMapping'])->find($id);
        $pageTitle = __('messages.update_form_title', ['form' => __('messages.department')]);

        if ($departmentData == null) {
            $pageTitle = __('messages.add_button_form', ['form' => __('messages.department')]);
            $departmentData = new Department();
        }
        $deptShiftHours = $departmentData->departmentShiftHoursMapping->pluck('shift_hours_id')->toArray();
        $deptPriceType = $departmentData->departmentPriceTypesMapping->pluck('price_types_id');
        $deptMaterialType = $departmentData->departmentMaterialUnitsMapping->pluck('material_unit_id');
        $deptType = $departmentData->departmentTypes->pluck('id');

        $requiredDocuments = Certificate::where('is_required', 1)->where('status', 1)->get([
            'id',
            'name'
        ]);

        $optionalDocuments = Certificate::where('is_required', 0)->where('status', 1)->get([
            'id',
            'name'
        ]);

        $requiredCertificates = $departmentData->requiredDepartmentCertificates->pluck('id');
        $optionalCertificates = $departmentData->optionalDepartmentCertificates->pluck('id');

        $shifTypetData = ShiftType::select('id', 'name')->with('shiftHours')->get();
        $priceTypeData = PriceTypes::select('id', 'name')->get();
        $departmentTypeData = Type::get(['id', 'name']);
        $materialUnitData = MaterialUnits::select('id', 'name')->get();
        return view('department.create', compact('pageTitle', 'departmentData', 'auth_user', 'shifTypetData', 'priceTypeData', 'materialUnitData', 'deptShiftHours', 'deptPriceType', 'deptMaterialType', 'departmentTypeData', 'deptType', 'requiredCertificates', 'optionalCertificates', 'optionalDocuments', 'requiredDocuments'));
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

        if ($request->id == null) {
            $data['status'] = !empty($data['status']) ? $data['status'] : 1;
        }
        $data['is_transport_option'] = $request->has('is_transport_option') ? 1 : 0;
        $data['is_site_visit'] = $request->has('is_site_visit') ? 1 : 0;
        $data['is_experience'] = $request->has('is_experience') ? 1 : 0;
        $data['is_expected_salary'] = $request->has('is_expected_salary') ? 1 : 0;
        $data['is_relocate'] = $request->has('is_relocate') ? 1 : 0;
        $data['is_used_travelling'] = $request->has('is_used_travelling') ? 1 : 0;
        $data['is_notice_joining'] = $request->has('is_notice_joining') ? 1 : 0;
        $data['is_business_name'] = $request->has('is_business_name') ? 1 : 0;
        $data['is_designation'] = $request->has('is_designation') ? 1 : 0;
        $data['is_preferred'] = $request->has('is_preferred') ? 1 : 0;
        $data['is_qualification'] = $request->has('is_qualification') ? 1 : 0;
        $data['is_plot_area'] = $request->has('is_plot_area') ? 1 : 0;
        $data['is_advance_payment'] = $request->has('is_advance_payment') ? 1 : 0;
        $data['is_tax'] = $request->has('is_tax') ? 1 : 0;
        $data['is_interest_rates'] = $request->has('is_interest_rates') ? 1 : 0;
        $data['is_loan_process'] = $request->has('is_loan_process') ? 1 : 0;
        $data['is_time_slots'] = $request->has('is_time_slots') ? 1 : 0;
        $data['is_discount_enabled'] = $request->has('is_discount_enabled') ? 1 : 0;
        $data['is_price_enabled'] = $request->has('is_price_enabled') ? 1 : 0;
        $data['is_multiple_location'] = $request->has('is_multiple_location') ? 1 : 0;

        $result = Department::updateOrCreate(['id' => $request->id], $data);

        $result->departmentTypes()->sync($request->input('type_ids'));
        $result->requiredDepartmentCertificates()->sync($request->input('required_certificates'));
        $result->optionalDepartmentCertificates()->sync($request->input('optional_certificates'));

        if ($result->departmentShiftHoursMapping()->count() > 0)
        {
            $result->departmentShiftHoursMapping()->delete();
        }
        if($request->shift_hours_id != null) {

            foreach($request->shift_hours_id as $tax) {
                if($tax > 0){
                    $provider_tax = [
                        'department_id'   => $result->id,
                        'shift_hours_id'   => $tax,
                    ];
                    $result->departmentShiftHoursMapping()->insert($provider_tax);
                }
            }
        }

        if ($result->departmentPriceTypesMapping()->count() > 0)
        {
            $result->departmentPriceTypesMapping()->delete();
        }
        if (!empty($request->price_types_id) && array_filter($request->price_types_id)) {

            foreach($request->price_types_id as $tax) {
                if($tax > 0){
                    $provider_tax = [
                        'department_id'   => $result->id,
                        'price_types_id'   => $tax,
                    ];
                    $result->departmentPriceTypesMapping()->insert($provider_tax);
                }
            }
        }

        if($result->departmentMaterialUnitsMapping()->count() > 0)
        {
            $result->departmentMaterialUnitsMapping()->delete();
        }
        if (!empty($request->material_unit_id) && array_filter($request->material_unit_id)) {

            foreach($request->material_unit_id as $tax) {
                if($tax > 0){
                    $provider_tax = [
                        'department_id'   => $result->id,
                        'material_unit_id'   => $tax,
                    ];
                    $result->departmentMaterialUnitsMapping()->insert($provider_tax);
                }
            }
        }

        storeMediaFile($result, $request->department_image, 'department_image');

        $message = __('messages.save_form', ['form' => __('messages.department')]);

        return redirect(route('department.index'))->withSuccess($message);

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $auth_user = authSession();
        $departmentData = Booking::with('bookingExtraCharge')->myBooking()->find($id);
        $user = auth()->user();
        $user->last_notification_seen = now();
        $user->save();

        if (count($user->unreadNotifications) > 0) {

            foreach ($user->unreadNotifications as $notifications) {

                if ($notifications['data']['id'] == $id) {

                    $notification = $user->unreadNotifications->where('id', $notifications['id'])->first();
                    if ($notification) {
                        $notification->markAsRead();
                    }
                }

            }

        }

        $departmentData = Booking::with('bookingExtraCharge', 'payment')->myBooking()->find($id);

        $tabpage = 'info';
        if (empty($departmentData)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.booking')]);
            return redirect(route('booking.index'))->withError($msg);
        }
        if (count($auth_user->unreadNotifications) > 0) {
            $auth_user->unreadNotifications->where('data.id', $id)->markAsRead();
        }

        $pageTitle = __('messages.view_form_title', ['form' => __('messages.booking')]);
        return view('booking.view', compact('pageTitle', 'departmentData', 'auth_user', 'tabpage'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $auth_user = authSession();

        $departmentData = Booking::myBooking()->find($id);

        $pageTitle = __('messages.update_form_title', ['form' => __('messages.booking')]);
        $relation = [
            'status' => BookingStatus::where('status', 1)->orderBy('sequence', 'ASC')->get()->pluck('label', 'value'),
        ];
        return view('booking.edit', compact('pageTitle', 'departmentData', 'auth_user') + $relation);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (demoUserPermission()) {
            return redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $department = Department::find($id);
        $msg = __('messages.msg_fail_to_delete', ['item' => __('messages.department')]);

        if ($department != '') {
            $department->delete();
            $msg = __('messages.msg_deleted', ['name' => __('messages.department')]);
        }
        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function bookingAssignForm(Request $request)
    {

        $departmentData = Booking::find($request->id);
        $pageTitle = __('messages.assign_form_title', ['form' => __('messages.booking')]);
        return view('booking.assigned_form', compact('departmentData', 'pageTitle'));
    }

    public function bookingAssigned(Request $request)
    {
        $auth_user = authSession();
        $departmentData = Booking::find($request->id);

        $assigned_handyman_ids = [];
        if ($departmentData->handymanAdded()->count() > 0) {
            $assigned_handyman_ids = $departmentData->handymanAdded()->pluck('handyman_id')->toArray();
            $departmentData->handymanAdded()->delete();
            $message = __('messages.transfer_to_handyman');
            $activity_type = 'transfer_booking';
        } else {
            $message = __('messages.assigned_to_handyman');
            $activity_type = 'assigned_booking';
        }

        $assign_handyman_id = [];
        if (!$request->is('api/*') && isset($request->provider_id)) {
            if ($request->handyman_id == null) {
                $assign_handyman_id = $request->provider_id;
                $departmentData->provider_id = $request->provider_id[0];
                $departmentData->update();
            } else {
                $assign_handyman_id = $request->handyman_id;
            }

        } elseif (!$request->is('api/*') && isset($request->assignto) && $request->assignto == 'myself') {
            $assign_handyman_id = ($auth_user->user_type == 'provider') ? [$auth_user->id] : [];
        } elseif ($request->handyman_id != null) {
            $assign_handyman_id = $request->handyman_id;
        }

        $remove_notification_id = [];
        if (count($assign_handyman_id) > 0) {
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
            $search = "id" . '":' . $departmentData->id;

            Notification::whereIn('notifiable_id', $remove_notification_id)
                ->whereJsonContains('data->id', $departmentData->id)
                ->delete();
        }

        $departmentData->status = 'accept';
        $departmentData->save();

        $activity_data = [
            'activity_type' => $activity_type,
            'booking_id' => $departmentData->id,
            'booking' => $departmentData,
        ];

        saveBookingActivity($activity_data);

        $message = __('messages.save_form', ['form' => __('messages.booking')]);
        if ($request->is('api/*')) {
            if ($request->has('user_type') && $request->user_type == 'provider') {
                $departmentData->provider_id = $request->handyman_id[0];
                $departmentData->update();
            }
            return comman_message_response($message);
        }

        return response()->json(['status' => true, 'event' => 'callback', 'message' => $message]);
    }

    public function action(Request $request)
    {
        $id = $request->id;
        $type = $request->type;
        $departmentData = Department::withTrashed()->where('id', $id)->first();
        $msg = __('messages.not_found_entry', ['name' => __('messages.department')]);
        if ($request->type === 'restore') {
            if ($departmentData != '') {
                $departmentData->restore();
                $msg = __('messages.msg_restored', ['name' => __('messages.department')]);
            }
        }
        if ($request->type === 'forcedelete') {
            $departmentData->forceDelete();
            $msg = __('messages.msg_forcedelete', ['name' => __('messages.department')]);
        }

        return comman_custom_response(['message' => $msg, 'status' => true]);
    }

    public function bookingDetails(Request $request, $id)
    {
        $auth_user = authSession();
        $providerdata = User::with('providerBooking')->where('user_type', 'provider')->where('id', $id)->first();
        $earningData = array();
        foreach ($providerdata->providerBooking as $booking) {
            $booking_id = $booking->id;
            $provider_name = optional($booking->provider)->display_name ?? '-';
            $provider_contact = optional($booking->provider)->contact_number ?? '-';
            $amount = $booking->amount;
            $payment_status = optional($booking->payment)->payment_status ?? '-';
            $start_at = $booking->start_at;
            $end_at = $booking->end_at;
            $earningData[] = [
                'provider_id' => $providerdata->id,
                'booking_id' => $booking->id,
                'provider_name' => $provider_name,
                'provider_contact' => $provider_contact,
                'amount' => $amount,
                'payment_status' => $payment_status,
                'start_at' => $start_at,
                'end_at' => $end_at,
            ];
        }
        if ($request->ajax()) {
            return Datatables::of($earningData)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '-';
                    $booking_id = $row['booking_id'];
                    $btn = "<a href=" . route('booking.show', $booking_id) . "><i class='fas fa-eye'></i></a>";
                    return $btn;
                })
                ->editColumn('payment_status', function ($row) {
                    $payment_status = $row['payment_status'];
                    if ($payment_status == 'pending') {
                        $status = '<span class="badge badge-danger">' . __('messages.pending') . '</span>';
                    } else {
                        $status = '<span class="badge badge-success">' . __('messages.paid') . '</span>';
                    }
                    return $status;
                })
                ->editColumn('amount', function ($row) {
                    return $row['amount'] ? getPriceFormat($row['amount']) : '-';
                })
                ->rawColumns(['action', 'payment_status', 'amount'])
                ->make(true);
        }
        if (empty($providerdata)) {
            $msg = __('messages.not_found_entry', ['name' => __('messages.provider')]);
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title', ['form' => __('messages.provider')]);
        return view('booking.details', compact('pageTitle', 'earningData', 'auth_user', 'providerdata'));
    }

    public function bookingstatus(Request $request, $id)
    {
        $tabpage = $request->tabpage;
        $auth_user = authSession();
        $user_id = $auth_user->id;
        $user_data = User::find($user_id);
        $departmentData = Booking::with('handymanAdded', 'payment', 'bookingExtraCharge', 'bookingAddonService')->myBooking()->find($id);
        switch ($tabpage) {
            case 'info':
                $data = view('booking.' . $tabpage, compact('user_data', 'tabpage', 'auth_user', 'departmentData'))->render();
                break;
            case 'status':
                $data = view('booking.' . $tabpage, compact('user_data', 'tabpage', 'auth_user', 'departmentData'))->render();
                break;
            default:
                $data = view('booking.' . $tabpage, compact('tabpage', 'auth_user', 'departmentData'))->render();
                break;
        }
        return response()->json($data);
    }

    public function createPDF($id)
    {
        $data = AppSetting::take(1)->first();
        $departmentData = Booking::with('handymanAdded', 'payment', 'bookingExtraCharge')->myBooking()->find($id);
        $pdf = Pdf::loadView('booking.invoice', ['departmentData' => $departmentData, 'data' => $data]);
        return $pdf->download('invoice.pdf');
    }

    public function updateStatus(Request $request)
    {

        switch ($request->type) {
            case 'payment':
                $data = Payment::where('booking_id', $request->bookingId)->update(['payment_status' => $request->status]);
                break;
            default:

                $data = Booking::find($request->bookingId)->update(['status' => $request->status]);
                break;

        }

        return comman_custom_response(['message' => 'Status Updated', 'status' => true]);
    }
}
