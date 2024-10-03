<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PaymentHistory;
use App\Models\Payment;
use App\Models\AppSetting;
use App\DataTables\PaymentDataTable;
use App\DataTables\CashPaymentDataTable;
use App\DataTables\PaymentHistoryDataTable;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(PaymentDataTable $dataTable,Request $request)
    {
        $pageTitle = __('messages.list_form_title',['form' => __('messages.payment')] );
        $assets = ['datatable'];
        return $dataTable->render('payment.index', compact('pageTitle','assets'));
    }

    public function cashIndex(PaymentHistoryDataTable $dataTable ,Request $request)
    {
        $pageTitle = __('messages.list_form_title',['form' => __('messages.cash_history')] );
        $assets = ['datatable'];
        return $dataTable->with('id',$request->id)->render('paymenthistory.index', compact('pageTitle','assets'));
    }

    public function cashDatatable(CashPaymentDataTable $dataTable,Request $request){
        $pageTitle = __('messages.list_form_title',['form' => __('messages.cash_payment')] );
        $assets = ['datatable'];
        return $dataTable->render('payment.cash', compact('pageTitle','assets'));
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
        //
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
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $document = Payment::find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.payment')] );

        if( $document!='') {

            $document->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.payment')] );
        }
        if(request()->is('api/*')) {
            return comman_message_response($msg);
		}
        return comman_custom_response(['message'=> $msg, 'status' => true]);
    }

    public function cashApprove($id){
        $admin = AppSetting::first();

        $paymentdata = Payment::where('id',$id)->first();
        $parent_payment_history = PaymentHistory::where('status','pending_by_admin')
        ->where('payment_id',$id)->first();



        $payment_history = [
            'payment_id' => $id,
            'booking_id' => $paymentdata->booking_id,
            'action' => config('constant.PAYMENT_HISTORY_ACTION.ADMIN_APPROVED_CASH'),
            'type' => $parent_payment_history->payment_type,
            'sender_id' => $parent_payment_history->sender_id,
            'receiver_id' => admin_id(),
            'total_amount' => $paymentdata->total_amount,
            'text' =>  __('messages.cash_approved',['amount' => getPriceFormat((float)$paymentdata->total_amount),'name' => get_user_name(admin_id())]),
            'status' => config('constant.PAYMENT_HISTORY_STATUS.APPROVED_ADMIN'),
            'parent_id' => $parent_payment_history->parent_id
        ];

        date_default_timezone_set( $admin->time_zone ?? 'UTC');
        $payment_history['datetime'] = date('Y-m-d H:i:s');

        if(!empty($paymentdata->txn_id)){
            $payment_history['txn_id'] =$paymentdata->txn_id;
        }
        if(!empty($paymentdata->other_transaction_detail)){
            $payment_history['other_transaction_detail'] =$paymentdata->other_transaction_detail;
        }
        $res = PaymentHistory::create($payment_history);

        $parent_record = PaymentHistory::where('parent_id',$parent_payment_history->parent_id)->first();

        $parent_payment_history->status = 'approved_by_admin';
        $parent_payment_history->update();

        $parent_record->status = 'approved_by_admin';
        $parent_record->update();


        $paymentdata->payment_status = 'paid';
        $paymentdata->update();

        $msg = __('messages.approve_successfully');
        return redirect()->back()->withSuccess($msg);
    }

}
