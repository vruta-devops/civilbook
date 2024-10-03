{{ Form::model($payment_data, ['method' => 'POST','route' => ['paymentsettingsUpdates'],'enctype'=>'multipart/form-data','data-toggle'=>'validator']) }}

{{ Form::hidden('id', null, array('placeholder' => 'id','class' => 'form-control')) }}
{{ Form::hidden('type', $tabpage, array('placeholder' => 'id','class' => 'form-control')) }}
 <div class="row">
    <div class="form-group col-md-12" >
        <label for="enable_instamojo">{{__('messages.payment_on',['gateway'=>__('messages.instamojo')])}}</label>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="status" id="enable_instamojo" {{!empty($payment_data) && $payment_data->status == 1 ? 'checked' : ''}}>
            <label class="custom-control-label" for="enable_instamojo"></label>
        </div>
    </div>
 </div>
 <div class="row" id='enable_instamojo_payment'>
    <div class="form-group col-md-12">
        <label class="form-control-label">{{__('messages.payment_option',['gateway'=>__('messages.instamojo')])}}</label><br/>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" class="form-check-input is_test" value="on" name="is_test" data-type="is_test_mode" {{!empty($payment_data) && $payment_data->is_test == 1 ? 'checked' :''}}>{{__('messages.is_test_mode')}}
            </label>
        </div>
        <div class="form-check-inline">
            <label class="form-check-label">
                <input type="radio" class="form-check-input is_test" value="off" name="is_test" data-type="is_live_mode" {{!empty($payment_data) && $payment_data->is_test == 0 ? 'checked' :''}}>{{__('messages.is_live_mode')}}
            </label>
        </div>
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('title',trans('messages.gateway_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('title',old('title'),['id'=>'title','placeholder' => trans('messages.title'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('instamojo_url',trans('messages.instamojo_url').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('instamojo_url',old('instamojo_url'),['id'=>'instamojo_url','placeholder' => trans('messages.instamojo_url'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('instamojo_api_key',trans('messages.instamojo_api_key').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('instamojo_api_key',old('instamojo_api_key'),['id'=>'instamojo_api_key','placeholder' => trans('messages.instamojo_api_key'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('instamojo_auth_token',trans('messages.instamojo_auth_token').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('instamojo_auth_token',old('instamojo_auth_token'),['id'=>'instamojo_auth_token','placeholder' => trans('messages.instamojo_auth_token'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
 </div>
{{ Form::submit(__('messages.save'), ['class'=>"btn btn-md btn-primary float-md-right"]) }}
{{ Form::close() }}
<script>
var enable_instamojo = $("input[name='status']").prop('checked');
checkPaymentTabOption(enable_instamojo);

$('#enable_instamojo').change(function(){
    value = $(this).prop('checked') == true ? true : false;
    checkPaymentTabOption(value);
});
function checkPaymentTabOption(value){
    if(value == true){
        $('#enable_instamojo_payment').removeClass('d-none');
    }else{
        $('#enable_instamojo_payment').addClass('d-none');
    }
}

var get_value = $('input[name="is_test"]:checked').data("type");
getConfig(get_value)
$('.is_test').change(function(){
    value = $(this).prop('checked') == true ? true : false;
    type = $(this).data("type");
    getConfig(type)

});

function getConfig(type){
    var _token   = $('meta[name="csrf-token"]').attr('content');
    var page =  "{{$tabpage}}";
    $.ajax({
        url: "/get_payment_config",
        type:"POST",
        data:{
          type:type,
          page:page,
          _token: _token
        },
        success:function(response){
            var obj = '';
            var instamojo_url=instamojo_api_key=instamojo_auth_token=title = '';

            if(response){
            
                if(response.data.type == 'is_test_mode'){
                    obj = JSON.parse(response.data.value);
                }else{
                    obj = JSON.parse(response.data.live_value);
                }

                if(response.data.title != ''){
                    title = response.data.title
                }
                
                if(obj !== null){
                    var instamojo_url = obj.instamojo_url;
                    var instamojo_api_key = obj.instamojo_api_key;
                    var instamojo_auth_token = obj.instamojo_auth_token;
                }

                $('#instamojo_url').val(instamojo_url)
                $('#instamojo_api_key').val(instamojo_api_key)
                $('#instamojo_auth_token').val(instamojo_auth_token)
                $('#title').val(title)
            
            }
        },
        error: function(error) {
         console.log(error);
        }
    });
}

</script>