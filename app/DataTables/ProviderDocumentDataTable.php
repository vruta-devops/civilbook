<?php

namespace App\DataTables;

use App\Models\Certificate;
use App\Models\Service;
use App\Models\ServiceCertificate;
use App\Models\User;
use App\Traits\DataTableTrait;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProviderDocumentDataTable extends DataTable
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
            ->editColumn('is_verified' , function ($provider_document){
                if(auth()->user()->hasAnyRole(['provider','demo_provider'])){
                    if ($provider_document->is_approved == 0) {
                        $status = '<span class="badge badge-danger">'.__('messages.unverified').'</span>';
                    }else{
                        $status = '<span class="badge badge-success">'.__('messages.verified').'</span>';
                    }
                    return $status;
                }
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input bg-primary change_status" data-type="provider_is_verified" data-name="provider_is_verified" ' . ($provider_document->is_approved ? "checked" : "") . '  value="' . $provider_document->id . '" id="' . $provider_document->id . '" data-id="' . $provider_document->id . '">
                        <label class="custom-control-label" for="'.$provider_document->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';

            })
            ->editColumn('provider_id' , function ($provider_document){
                $provider = User::where('id', $provider_document->provider_id)->first();
                return ($provider_document->provider_id != null) ? '<a class="btn-link btn-link-hover" href=' . route('providerdocument.create', ['id' => $provider_document->id]) . '>' . $provider->first_name . '</a>' : '';
            })
            ->editColumn('service_id', function ($provider_document) {
                $service = Service::where('id', $provider_document->service_id)->withTrashed()->first();
                return $service->name;
            })
            ->editColumn('document_id' , function ($provider_document){
                $serviceCertificate = Certificate::where('id', $provider_document->certificate_id)->first();
                return $serviceCertificate->name;
            })
            ->filterColumn('document_id', function ($query, $keyword) {
                $query->whereHas('certificate', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('service_id', function ($query, $keyword) {
                $query->whereHas('service', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('provider_id',function($query,$keyword){
                $query->whereHas('provider', function ($q) use ($keyword) {
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->addColumn('action', function($provider_document){
                return view('providerdocument.action',compact('provider_document'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['provider_id', 'action','is_verified']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ServiceCertificate $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ServiceCertificate $model)
    {
        if (getLoggedUserType() === 'provider') {
            return $model->where('provider_id', getLoggedUserId())->newQuery()->orderByRaw("
            CASE
                WHEN is_approved = 0 THEN 0
                ELSE 1
            END,
            id DESC");
        }

        return $model->newQuery()->orderByRaw("
            CASE
                WHEN is_approved = 0 THEN 0
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
            Column::make('provider_id')
                ->title(__('messages.provider'))
                ->exportable(false)
                ->printable(false)
                ->orderable(),
            Column::make('service_id')
                ->title(__('messages.service'))
                ->orderable(),
            Column::make('document_id')
                ->title(__('messages.document'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false),
            Column::make('is_verified')
                ->searchable(false)
                ->exportable(false)
                ->printable(false)
                ->orderable(false),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Document_' . date('YmdHis');
    }
}
