<?php

namespace App\DataTables;
use App\Traits\DataTableTrait;

use App\Models\Service;
use App\Models\PackageServiceMapping;
use App\Models\PostJobServiceMapping;
use App\Models\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ServiceDataTable extends DataTable
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
            ->editColumn('name', function($service){
                $auth_user= authSession();
                if($auth_user->user_type=='admin' || $auth_user->user_type=='provider' && $service->admin_service_type!='common' && $service->provider_id==$auth_user->id)
                    return '<a class="btn-link btn-link-hover" href='.route('service.create', ['id' => $service->id]).'>'.$service->name.'</a>';
                else
                    return '<a class="btn-link btn-link-hover" href="javascript::void(0)">'.$service->name.'</a>';
            })
            ->editColumn('category_id' , function ($service){
                return ($service->category_id != null && isset($service->category)) ? $service->category->name : '-';
            })
            ->filterColumn('category_id',function($query,$keyword){
                $query->whereHas('category',function ($q) use($keyword){
                    $q->where('name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('provider_id' , function ($service){
                $provider_id = 0;
                if(isset($service->serviceProviderMapping))
                {
                    $provider_id = $service->serviceProviderMapping->pluck('provider_id');
                    $providers = User::whereIn('id',$provider_id)->take(1)->pluck('display_name')->implode(', ');
                    if(count($provider_id)>1)
                    {
                        $providers = $providers.' <span class="text-primary"> +'.((int)count($provider_id) - 1).' More</span>';
                    }
                }
                return count($provider_id)>0 ? $providers : '-';
            })
            ->filterColumn('provider_id',function($query,$keyword){
                $query->whereHas('providers',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('price' , function ($service){
                return getPriceFormat($service->price).'-'.ucFirst($service->type);
            })
            /*
            ->editColumn('discount' , function ($service){
                return $service->discount ? $service->discount .'%' : '-';
            })
            */
            ->editColumn('user_service_status', function ($service){
                $auth_user= authSession();
                if($auth_user->user_type=='admin')
                {
                    $disabled = $service->deleted_at ? 'disabled': '';
                    return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                            <div class="custom-switch-inner">
                                <input type="checkbox" class="custom-control-input change_status" '.$disabled.' data-type="user_service_status" '.(($service->user_service_status==1) ? "checked" : "").' value="'.$service->id.'" id="userstatus'.$service->id.'" data-id="'.$service->id.'" >
                                <label class="custom-control-label" for="userstatus'.$service->id.'" data-on-label="" data-off-label=""></label>
                            </div>
                            </div>';
                }
                else
                {
                    if($service->user_service_status == 1){
                        $userServiceStatus = '<span class="badge badge-success">'.__('messages.approved').'</span>';
                    }
                    else{
                        $userServiceStatus = '<span class="badge badge-warning">'.__('messages.waiting_approval').'</span>';
                    }
                    return  $userServiceStatus;
                }
            })
            ->editColumn('status' , function ($service){
                $disabled = $service->deleted_at ? 'disabled': '';
                return '<div class="custom-control custom-switch custom-switch-text custom-switch-color custom-control-inline">
                    <div class="custom-switch-inner">
                        <input type="checkbox" class="custom-control-input  change_status" '.$disabled.' data-type="service_status" '.($service->status ? "checked" : "").' value="'.$service->id.'" id="'.$service->id.'" data-id="'.$service->id.'" >
                        <label class="custom-control-label" for="'.$service->id.'" data-on-label="" data-off-label=""></label>
                    </div>
                </div>';
            })
            ->addColumn('action', function($service){
                return view('service.action',compact('service'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['name','action','status','user_service_status','provider_id']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Service $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Service $model)
    {
        if(auth()->user()->hasAnyRole(['admin'])){
            $model = $model->where('service_type','service')->withTrashed();
            if($this->provider_id !== null){
                $model =  $model->where('provider_id', $this->provider_id );
            }
            if($this->packageid !== null){
                $packageservice = PackageServiceMapping::where('service_package_id',$this->packageid)->pluck('service_id');
                $model =  $model->whereIn('id',  $packageservice  );
            }
            if($this->postjobid !== null)    {
                $postjobservice = PostJobServiceMapping::where('post_request_id', $this->postjobid)->pluck('service_id');
                $model = $model->whereIn('id', $postjobservice);
            }
        
        }
        return $model->list()->newQuery()->myService();
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
                ->title(__('messages.srno'))
                ->orderable(false),
            Column::make('name')
                ->title(__('messages.name')),
            Column::make('provider_id')
                ->title(__('messages.provider')),
            Column::make('category_id')
                ->title(__('messages.category')),
            Column::make('price')
                ->title(__('messages.price')),
            /*    
            Column::make('discount')
                ->title(__('messages.discount')),
            */
            Column::make('user_service_status')
                ->title(__('messages.approval')),
            Column::make('status')
                ->title(__('messages.user_service_status')),
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
        return 'Service_' . date('YmdHis');
    }
}
