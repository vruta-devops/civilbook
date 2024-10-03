<?php

namespace App\DataTables;

use App\Models\Booking;
use App\Traits\DataTableTrait;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DepartmentDataTable extends DataTable
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
            ->editColumn('id', function ($department) {
                return "<a href=" . route('department.show', $department->id) . ">" . $department->id . "</a>";
            })
            ->filterColumn('id', function ($query, $keyword) {
                $query->where('id', $keyword);
            })
            ->editColumn('name', function ($department) {
                return ($department->name != null) ? $department->name : '-';
            })
            ->editColumn('status', function ($department) {
                if ($department->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                } else {
                    return '<span class="badge badge-danger">In Active</span>';
                }
            })
            ->addColumn('action', function ($department) {
                return view('department.action', compact('department'))->render();
            })
            ->rawColumns(['id', 'action', 'status', 'name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Booking $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Booking $model)
    {
        if (auth()->user()->hasAnyRole(['admin'])) {
            $model = $model->withTrashed();
        }
        $model = $model->orderBy('created_at', 'desc');
        return $model->list();
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('name')
                ->title(__('messages.department_name')),
            Column::make('status')
                ->title(__('messages.status')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(30)
                ->addClass('justify-content-start')
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
        return 'Department_' . date('YmdHis');
    }
}
