{{ Form::model($payment_data, ['method' => 'POST','route' => ['paymentsettingsUpdates'],'enctype'=>'multipart/form-data','data-toggle'=>'validator']) }}

{{ Form::hidden('id', null, array('placeholder' => 'id','class' => 'form-control')) }}
{{ Form::hidden('type', $tabpage, array('placeholder' => 'id','class' => 'form-control')) }}
 <div class="row">
    <div class="form-group col-md-12" >
        <label for="enable_cashfree">{{__('messages.payment_on',['gateway'=>__('messages.cashfree')])}}</label>
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" name="status" id="enable_cashfree" {{!empty($payment_data) && $payment_data->status == 1 ? 'checked' : ''}}>
            <label class="custom-control-label" for="enable_cashfree"></label>
        </div>
    </div>
 </div>
 <div class="row" id='enable_cashfree_payment'>
    <div class="form-group col-md-12">
        <label class="form-control-label">{{__('messages.payment_option',['gateway'=>__('messages.cashfree')])}}</label><br/>
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
        {{ Form::label('cashfree_url',trans('messages.cashfree_url').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('cashfree_url',old('cashfree_url'),['id'=>'cashfree_url','placeholder' => trans('messages.cashfree_url'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('cashfree_app_secreat_key',trans('messages.cashfree_app_secreat_key').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('cashfree_app_secreat_key',old('cashfree_app_secreat_key'),['id'=>'cashfree_app_secreat_key','placeholder' => trans('messages.cashfree_app_secreat_key'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
    <div class="form-group col-md-12">
        {{ Form::label('cashfree_app_id',trans('messages.cashfree_app_id').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
        {{ Form::text('cashfree_app_id',old('cashfree_app_id'),['id'=>'cashfree_app_id','placeholder' => trans('messages.cashfree_app_id'),'class' =>'form-control','required']) }}
        <small class="help-block with-errors text-danger"></small>
    </div>
 </div>
{{ Form::submit(__('messages.save'), ['class'=>"btn btn-md btn-primary float-md-right"]) }}
{{ Form::close() }}
<script>
var enable_cashfree = $("input[name='status']").prop('checked');
checkPaymentTabOption(enable_cashfree);

$('#enable_cashfree').change(function(){
    value = $(this).prop('checked') == true ? true : false;
    checkPaymentTabOption(value);
});
function checkPaymentTabOption(value){
    if(value == true){
        $('#enable_cashfree_payment').removeClass('d-none');
    }else{
        $('#enable_cashfree_payment').addClass('d-none');
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
            var cashfree_url=cashfree_app_secreat_key=cashfree_app_id=title = '';

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
                    var cashfree_url = obj.cashfree_url;
                    var cashfree_app_secreat_key = obj.cashfree_app_secreat_key;
                    var cashfree_app_id = obj.cashfree_app_id;
                }

                $('#cashfree_url').val(cashfree_url)
                $('#cashfree_app_secreat_key').val(cashfree_app_secreat_key)
                $('#cashfree_app_id').val(cashfree_app_id)
                $('#title').val(title)
            
            }
        },
        error: function(error) {
         console.log(error);
        }
    });
}

</script>