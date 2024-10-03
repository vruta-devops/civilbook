<?php

namespace App\DataTables;

use App\Models\Booking;
use App\Traits\DataTableTrait;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DepartmentTypeDataTable extends DataTable
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
            ->editColumn('id', function ($departmentType) {
                return "<a href=" . route('department_type.show', $departmentType->id) . ">" . $departmentType->id . "</a>";
            })
            ->filterColumn('id', function ($query, $keyword) {
                $query->where('id', $keyword);
            })
            ->editColumn('name', function ($departmentType) {
                return ($departmentType->name != null) ? $departmentType->name : '-';
            })
            ->addColumn('action', function ($departmentType) {
                return view('departmentType.action', compact('departmentType'))->render();
            })
            ->rawColumns(['id', 'action', 'name']);
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
                ->title(__('messages.department_type_name')),
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
