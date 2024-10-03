<!-- Modal -->

<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">{{ $pageTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
       {{ Form::open(['route' => 'service.audio-service-change-status','method' => 'post','data-toggle'=>"validator"]) }}
        <div class="modal-body">

           {{ Form::hidden('id',$audioservicedata->id) }}
            <div class="row">
                
                <div class="col-md-12 form-group ">
                    {{ Form::label('status', __('messages.select_name',[ 'select' => __('messages.status') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                    <br />
                    {{ Form::select('status', [0=>'Pending',1=>'Approve',2=>'Reject'], $audioservicedata->status, [
                            'class' => 'select2js',
                            'id' => 'status_id',
                            'required',
                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.status') ]),
                        ]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-md btn-secondary" data-dismiss="modal">{{ trans('messages.close') }}</button>
            <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax" >{{ trans('messages.save') }}</button>
        </div>
        {{ Form::close() }}
    </div>
</div>
<script>
    $('#status_id').select2({
        width: '100%',
        placeholder: "{{ __('messages.select_name',['select' => __('messages.status')]) }}",
    });
</script>