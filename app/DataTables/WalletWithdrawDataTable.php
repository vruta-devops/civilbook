<?php

namespace App\DataTables;

use App\Models\WalletWithdraw;
use App\Traits\DataTableTrait;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class WalletWithdrawDataTable extends DataTable
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
            ->addColumn('user', function ($walletWithdraw) {
                return $walletWithdraw->user->first_name;
            })
            ->addColumn('account_holder_name', function ($walletWithdraw) {
                return $walletWithdraw->account_holder_name;
            })
            ->addColumn('bank_name', function ($walletWithdraw) {
                return $walletWithdraw->bank_name;
            })
            ->addColumn('branch_name', function ($walletWithdraw) {
                return $walletWithdraw->branch_name;
            })
            ->addColumn('account_number', function ($walletWithdraw) {
                return $walletWithdraw->account_number;
            })
            ->addColumn('account_type', function ($walletWithdraw) {
                return $walletWithdraw->account_type;
            })
            ->addColumn('ifsc_code', function ($walletWithdraw) {
                return $walletWithdraw->ifsc_code;
            })
            ->addColumn('amount', function ($walletWithdraw) {
                return $walletWithdraw->amount;
            })
            ->addColumn('user_type', function ($walletWithdraw) {
                return Str::title($walletWithdraw->user->user_type);
            })
            ->addColumn('status', function ($walletWithdraw) {
                $status = "<p id='status_" . $walletWithdraw->id . "'>" . Str::upper($walletWithdraw->status) . "</p>";

                if ($walletWithdraw->status === 'rejected') {
                    $status .= "<span id='reason_" . $walletWithdraw->id . "'><b>Reason:</b> " . $walletWithdraw->reject_reason . "</span>";
                }

                return $status;
            })
            ->addColumn('action', function ($wallet) {
                return view('wallet-withdraw.action', compact('wallet'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['user', 'action', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\WalletWithdraw $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(WalletWithdraw $model)
    {
        return $model->newQuery()->orderByRaw("
            CASE
                WHEN status = 'pending' THEN 0
                ELSE 1
            END,
            id DESC");
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
            Column::make('user')
                ->title(__('messages.full_name')),
            Column::make('user_type')
                ->title(__('messages.user_type')),
            Column::make('account_holder_name')
                ->title(__('messages.account_holder_name')),
            Column::make('bank_name')
                ->title(__('messages.bank_name')),
            Column::make('branch_name')
                ->title(__('messages.branch_name')),
            Column::make('account_number')
                ->title(__('messages.account_number')),
            Column::make('account_type')
                ->title(__('messages.account_type')),
            Column::make('ifsc_code')
                ->title(__('messages.ifsc_code')),
            Column::make('amount')
                ->title(__('messages.requested_amount')),
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
