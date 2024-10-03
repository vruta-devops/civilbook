<?php

namespace App\DataTables;
use App\Traits\DataTableTrait;

use App\Models\Booking;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use App\Models\BookingStatus;

class BookingDataTable extends DataTable
{
    use DataTableTrait;
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->editColumn('id' , function ($booking){
                return "<a href=" .route('booking.show', $booking->id).">".$booking->id ."</a>";
            })
            ->filterColumn('id',function($query,$keyword){
                $query->where('id', $keyword);
            })
            ->editColumn('customer_id' , function ($booking){
                return ($booking->customer_id != null && isset($booking->customer)) ? $booking->customer->display_name : '';
            })
            ->filterColumn('customer_id',function($query,$keyword){
                $query->whereHas('customer',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('service_id' , function ($booking){
                $service_name = ($booking->service_id != null && isset($booking->service)) ? $booking->service->name : "";
                return "<a href=" .route('booking.show', $booking->id).">".$service_name ."</a>";
            })
            ->filterColumn('service_id',function($query,$keyword){
                $query->whereHas('service',function ($q) use($keyword){
                    $q->where('name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('provider_id' , function ($booking){
                $provider_id = 0;
                $provider_id = $booking->providerAdded->pluck('provider_id')->count();
                if ($booking->status == 'pending' && isset($booking->providerAdded) && $provider_id > 1) {
                    $providers = '<span class="text-primary">' . ($provider_id) . ' Providers</span>';
                } else {
                    $providers = (!empty($booking->provider_id) && isset($booking->provider)) ? $booking->provider->display_name : '';
                }
                return $providers;
            })
            ->filterColumn('provider_id',function($query,$keyword){
                $query->whereHas('provider',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('status' , function ($booking){
                return bookingstatus(BookingStatus::bookingStatus($booking->status));
            })
            ->editColumn('payment_id' , function ($booking){
                $payment_status = optional($booking->payment)->payment_status;
                if($payment_status !== 'paid'){
                    $status = '<span class="badge badge-pay-pending">'.__('messages.pending').'</span>';
                }else{
                    $status = '<span class="badge badge-paid">'.__('messages.paid').'</span>';
                }
                return  $status;
            })
            ->filterColumn('payment_id',function($query,$keyword){
                $query->whereHas('payment',function ($q) use($keyword){
                    $q->where('payment_status','like',$keyword.'%');
                });
            })
            ->editColumn('total_amount' , function ($booking){
                return $booking->total_amount ? getPriceFormat($booking->total_amount) : '-';
            })
            ->addColumn('action', function($booking){
                return view('booking.action',compact('booking'))->render();
            })
            ->rawColumns(['id', 'action','status','payment_id','service_id','provider_id']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Booking $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Booking $model)
    {
        if(auth()->user()->hasAnyRole(['admin'])){
            $model = $model->withTrashed();
        }
        $model = $model->orderBy('created_at', 'desc');
        return $model->list()->newQuery()->myBooking();
    }
    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('id')
                ->title(__('messages.booking_id')),
            Column::make('service_id')
                ->title(__('messages.service')),
            Column::make('date')
                ->title(__('messages.booking_date')),
            Column::make('customer_id')
                ->title(__('messages.user')),
            Column::make('provider_id')
                ->title(__('messages.provider')),
            Column::make('status')
                ->title(__('messages.status')),
            Column::make('total_amount')
                ->title(__('messages.total_amount')),
            Column::make('payment_id')
                ->title(__('messages.payment_status')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(30)
                ->addClass('text-center')
                ->title(__('messages.action')),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Booking_' . date('YmdHis');
    }
}
