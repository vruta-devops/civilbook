<?php

namespace App\DataTables;
use App\Models\Wallet;
use App\Traits\DataTableTrait;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WalletDataTable extends DataTable
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
            ->editColumn('title', function($wallet){
                return '<a class="btn-link btn-link-hover" href='.route('wallet.show', $wallet->user_id).'>'.$wallet->title.'</a>';
            })
            ->editColumn('user_type', function ($wallet) {
                return Str::title($wallet->providers->user_type);
            })
            ->editColumn('amount' , function ($wallet){
                return getPriceFormat($wallet->amount);
            })
            ->editColumn('status' , function ($wallet){
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input bg-success change_status"  data-type="wallet_status" '.($wallet->status ? "checked" : "").' value="'.$wallet->id.'" id="'.$wallet->id.'" data-id="'.$wallet->id.'" >
                        <label class="custom-control-label" for="'.$wallet->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->addColumn('action', function($wallet){
                return view('wallet.action',compact('wallet'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title','action','status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Wallet $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Wallet $model)
    {
        return $model->newQuery();
    }
    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')
                ->searchable(false)
                ->title(__('messages.no'))
                ->orderable(false),
            Column::make('title')
                ->title(__('messages.full_name')),
            Column::make('user_type')
                ->title(__('messages.user_type')),
            Column::make('amount')
                ->title(__('messages.amount')),
            Column::make('status')
                ->title(__('messages.status')),
            Column::computed('action')
            ->exportable(false)
            ->printable(false)
            ->width(60)
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
        return 'Category_' . date('YmdHis');
    }
}
