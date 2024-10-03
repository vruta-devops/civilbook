<?php

namespace App\DataTables;
use App\Models\Slider;
use App\Traits\DataTableTrait;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SliderDataTable extends DataTable
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
            ->editColumn('title', function($slider){
                return '<a class="btn-link btn-link-hover" href='.route('slider.create', ['id' => $slider->id]).'>'.$slider->title.'</a>';
            })
            ->editColumn('provider', function ($slider) {
                return empty($slider->provider) ? '-' : $slider->provider->first_name;
            })
            ->filterColumn('provider', function ($query, $keyword) {
                $query->whereHas('provider', function ($q) use ($keyword) {
                    $q->where('first_name', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('address', function ($slider) {
                return empty($slider->providerAddress) ? '-' : $slider->providerAddress->address;
            })
            ->filterColumn('address', function ($query, $keyword) {
                $query->whereHas('providerAddress', function ($q) use ($keyword) {
                    $q->where('address', 'like', '%' . $keyword . '%');
                });
            })
            ->editColumn('status' , function ($slider){
                $disabled = $slider->deleted_at ? 'disabled': '';
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input bg-primary change_status" '.$disabled.' data-type="slider_status" '.($slider->status ? "checked" : "").' value="'.$slider->id.'" id="'.$slider->id.'" data-id="'.$slider->id.'">
                        <label class="custom-control-label" for="'.$slider->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->editColumn('address', function ($slider) {
                return "-";
            })
            ->editColumn('type_id' , function ($slider){
                return ($slider->type_id != null && isset($slider->service)) ? $slider->service->name : '';
            })
            ->filterColumn('type_id',function($query,$keyword){
                $query->whereHas('service',function ($q) use($keyword){
                    $q->where('name','like','%'.$keyword.'%');
                });
            })
            ->addColumn('action', function($slider){
                return view('slider.action',compact('slider'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['title','action','status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Slider $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Slider $model)
    {
        if(auth()->user()->hasAnyRole(['admin'])){
            $model = $model->withTrashed();
        }
        return $model->list()->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */

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
                  ->title(__('messages.srno'))
                  ->orderable(false)
                  ->width(60),
            Column::make('title')
                ->title(__('messages.slider_name')),
            // Column::make('type'),
            Column::make('provider')
                ->title(__('messages.title_name', ['title' => __('messages.provider')])),
            Column::make('address')
                ->title(__('messages.address'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false),
            Column::make('type_id')
                ->title(__('messages.title_name',['title' => __('messages.service')])),
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
        return 'Slider_' . date('YmdHis');
    }
}
