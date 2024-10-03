<?php

namespace App\DataTables;
use App\Traits\DataTableTrait;
use App\Models\AudioServiceRequest;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AudioServiceDataTable extends DataTable
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
            ->editColumn('user_id' , function ($audio){
                return ($audio->user_id != null && isset($audio->user)) ? $audio->user->display_name : '-';
            })
            ->filterColumn('user_id',function($query,$keyword){
                $query->whereHas('user',function ($q) use($keyword){
                    $q->where('display_name','like','%'.$keyword.'%');
                });
            })
            ->editColumn('contact_number' , function ($audio){
                return ($audio->user != null && isset($audio->user)) ? $audio->user->contact_number : '-';
            })
            ->filterColumn('contact_number',function($query,$keyword){
                $query->whereHas('user',function ($q) use($keyword){
                    $q->where('contact_number','like','%'.$keyword.'%');
                });
            })
            ->editColumn('audio' , function ($audio){
                return '<audio controls>
                        <source src="'.getSingleMedia($audio,'audio',null).'" type="audio/mpeg"> 
                        </audio>';
            })
            
            ->editColumn('description' , function ($audio){
                return $audio->description ? $audio->description : '-';
            })
            ->addColumn('datetime', function($audio){
                return dateAgoFormate($audio->created_at);
            })
            ->editColumn('status' , function ($audio){
                if($audio->status == 0){
                    $status = '<span class="badge badge-warning">'.__('messages.pending').'</span>';
                }elseif($audio->status == 1){
                    $status = '<span class="badge badge-success">'.__('messages.approve').'</span>';
                }elseif($audio->status == 2){
                    $status = '<span class="badge badge-danger">'.__('messages.reject').'</span>';
                }elseif($audio->status == 3){
                    $status = '<span class="badge badge-light">'.__('messages.delete').'</span>';
                }
                return  $status;
            })
            ->addColumn('action', function($audio){
                return view('service.audio_service_action',compact('audio'))->render();
            })
            ->addIndexColumn()
            ->rawColumns(['action', 'status', 'audio']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\AudioServiceRequest $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AudioServiceRequest $model)
    {

        if(auth()->user()->hasAnyRole(['admin'])){
            $model = $model->withTrashed();
        }
        $model = $model->orderBy('created_at', 'desc');
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
                ->title(__('messages.srno'))
                ->orderable(false),
            Column::make('user_id')
                ->title(__('messages.user')),
            Column::make('contact_number')
                ->title(__('messages.contact_number'))
                ->orderable(false),
            Column::make('audio')
                ->title(__('messages.audio')),
            Column::make('description')
                ->title(__('messages.description')),
            Column::make('datetime'),
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
        return 'Audio_' . date('YmdHis');
    }
}
