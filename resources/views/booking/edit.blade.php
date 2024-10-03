@php
$user_type = auth()->user()->user_type;
@endphp
<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('booking.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if($auth_user->can('booking list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($bookingdata,['method' => 'patch', 'route'=>['booking.update',$bookingdata->id], 'data-toggle'=>"validator" ,'id'=>'booking'] ) }}
                        {{ Form::hidden('id') }}
                        <div class="row">
                            <div class="form-group col-md-4">

                                {{ Form::label('status', __('messages.select_name',[ 'select' => __('messages.status') ]),['class'=>'form-control-label']) }}
                                <br />
                                {{ Form::select('status',$status,old('status'),[ 'id' => 'status' ,'class' =>'form-control select2js booking_status']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('date',__('messages.date').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('date',old('date'),['placeholder' => __('messages.date'),'class' =>'form-control datetimepicker','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('start_at',__('messages.start_at'),['class'=>'form-control-label']) }}
                                {{ Form::text('start_at',old('start_at'),['placeholder' => __('messages.start_at'),'class' =>'form-control datetimepicker']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('end_at',__('messages.end_at'),['class'=>'form-control-label']) }}
                                {{ Form::text('end_at',old('end_at'),['placeholder' => __('messages.end_at'),'class' =>'form-control datetimepicker']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            @if($bookingdata->payment_id != null)
                            <div class="form-group col-md-4">
                                {{ Form::label('payment_status',__('messages.payment_status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('payment_status',['pending' => __('messages.pending') , 'paid' => __('messages.paid') ,'failed' => __('messages.failed') ],optional($bookingdata->payment)->payment_status,[ 'id' => 'payment_status' ,'class' =>'form-control select2js','required']) }}
                            </div>
                            @endif
                            @if($bookingdata->handymanAdded->count() == 0)
                            @hasanyrole('admin|demo_admin|provider')
                            @if($user_type=='admin')
                            <div class="form-group col-md-4">
                                {{ Form::label('provider_id', __('messages.select_name',[ 'select' => __('messages.provider') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                @php
                                $provider_route = route('ajax-list', ['type' => 'provider', 'booking_id' => $bookingdata->id ]);
                                @endphp
                                {{ Form::select('provider_id[]', [], [], [
                                    'class' => 'select2js provider',
                                    'id' => 'provider_id',
                                    'required',
                                    'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider') ]),
                                    'data-ajax--url' => $provider_route,
                                        ]) }}
                            </div>
                            @elseif($user_type=='provider')
                            <div class="form-group col-md-4 ">
                                {{ Form::label('assignto', __('messages.select_name',[ 'select' => __('messages.assign_to')]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                {{ Form::select('assignto', ['myself'=>'My Self','handyman'=>'Handyman'], '', [
                                                'class' => 'select2js form-group assignto',
                                                'id' =>'assignto',
                                                'required',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.assign_to')]),
                                            ]) }}
                            </div>
                            @endif
                            <div class="form-group col-md-4" id="assign-to-handyman">
                                {{ Form::label('handyman_id', __('messages.select_name',[ 'select' => __('messages.handyman') ]).' <span class="text-danger handyman-required">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                @php
                                $assigned_handyman = $bookingdata->handymanAdded->mapWithKeys(function ($item) {
                                return [$item->handyman_id => optional($item->handyman)->display_name];
                                });
                                @endphp
                                {{ Form::select('handyman_id[]', $assigned_handyman, $bookingdata->handymanAdded->pluck('handyman_id'), [
                                    'class' => 'select2js handyman',
                                    'id' => 'handyman_id',
                                    'required',
                                    'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.handyman') ]),
                                    ]) }}
                            </div>
                            @endhasanyrole
                            @endif
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                {{ Form::label('description',__('messages.description'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('description', null, ['class'=>"form-control textarea" , 'rows'=>3  , 'placeholder'=> __('messages.description') ]) }}
                            </div>
                            <div class="form-group col-md-6 reason">
                                {{ Form::label('reason',__('messages.reason'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('reason', null, ['class'=>"form-control textarea" , 'rows' => 3, 'placeholder'=> __('messages.reason') ]) }}
                            </div>
                        </div>
                        {{ Form::submit( __('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('bottom_script')
    <script>
        (function($) {
            "use strict";
            $(document).ready(function() {
                // $('#status').attr("disabled", "true");

                /*  changeReason(status);

                 $("#status").change(function() {
                     changeReason(this.value)
                 });

                 function changeReason(status)
                 {
                     if (jQuery.inArray(status, ['hold', 'in_progress','failed']) !== -1) {
                         $('.reason').removeClass('d-none');
                     }else{
                         $('.reason').addClass('d-none');
                     }
                 } */
                var user_type = <?php echo ("'" . $auth_user->user_type . "'") ?>;

                if (user_type == 'admin') {
                    $('#handyman_id').removeAttr('required');
                    $('.handyman-required').addClass('d-none');
                }
                $(function() {
                    $("#assignto").trigger('change');
                });

                $(document).on('change', '#assignto', function() {
                    if ($(this).val() == 'handyman') {
                        getHandyman(<?php echo ($auth_user->id) ?>);
                        $('#handyman_id').attr("required", true);
                        $('#assign-to-handyman').removeClass('d-none');
                    } else {
                        $('#handyman_id').removeAttr('required');
                        $('#assign-to-handyman').addClass('d-none');
                        $("#handyman_id").val('').trigger('change');
                    }
                });

                $(document).on('change', '#provider_id', function() {
                    getHandyman($(this).val());
                });

                function getHandyman(provider_id, booking_id = "") {

                    var get_handyman_list;
                    var userType = $('.row').attr('attr-user');
                    if (booking_id == '') {
                        get_handyman_list = "{{ route('ajax-list', [ 'type' => 'handyman','provider_id' =>'']) }}" + provider_id;
                    } else {
                        get_handyman_list = "{{ route('ajax-list', [ 'type' => 'handyman','provider_id' => " + provider_id + ", 'booking_id' =>" + booking_id + "]) }}";
                    }
                    get_handyman_list = get_handyman_list.split('amp;').join('');
                    $("#handyman_id").empty();
                    $.ajax({
                        url: get_handyman_list,
                        success: function(result) {
                            $('#handyman_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.handyman')]) }}",
                                data: result.results
                            });
                            $('#handyman_id').val('').trigger('change');
                        }
                    });
                }
            });
        })(jQuery);
    </script>
    @endsection
</x-master-layout>