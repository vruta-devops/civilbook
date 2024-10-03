<!-- Modal -->
@php
$user_type = auth()->user()->user_type;
@endphp
<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">{{ $pageTitle }}</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        {{ Form::open(['route' => 'booking.assigned','method' => 'post','data-toggle'=>"validator"]) }}
        <div class="modal-body">

            {{ Form::hidden('id',$bookingdata->id) }}

            <div class="row" attr-user-id="{{auth()->user()->id}}" attr-user-type="{{ $user_type }}" attr-provider-id="{{ $bookingdata->provider_id }}" attr-booking-address-id="{{ $bookingdata->booking_address_id  }}">
                @if($user_type=='admin')
                <div class="col-md-12 form-group">
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
                <div class="col-md-12 form-group ">
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
                <div class="col-md-12 form-group" id="assign-to-handyman">
                    {{ Form::label('handyman_id', __('messages.select_name',[ 'select' => __('messages.handyman') ]).' <span class="text-danger handyman-required">*</span>',['class'=>'form-control-label'],false) }}
                    <br />
                    {{ Form::select('handyman_id[]', [], [], [
                            'class' => 'select2js handyman',
                            'id' => 'handyman_id',
                            'required',
                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.handyman') ]),
                        ]) }}
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-md btn-secondary" data-dismiss="modal">{{ trans('messages.close') }}</button>
            <button type="submit" class="btn btn-md btn-primary" id="btn_submit" data-form="ajax">{{ trans('messages.save') }}</button>
        </div>
        {{ Form::close() }}
    </div>
</div>
<script>
    $('#handyman_id').select2({
        width: '100%',
        placeholder: "{{ __('messages.select_name',['select' => __('messages.handyman')]) }}",
    });
    $('#provider_id').select2({
        width: '100%',
        placeholder: "{{ __('messages.select_name',['select' => __('messages.provider')]) }}",
    });
    $('#assignto').select2({
        width: '100%',
    });
    var user_type = $('.row').attr('attr-user-type');
    var provider_id = $('.row').attr('attr-provider-id');
    var user_id = $('.row').attr('attr-user-id');
    var booking_address_id = $('.row').attr('attr-booking-address-id');
    if (user_type == 'admin') {
        $('#handyman_id').removeAttr('required');
        $('.handyman-required').addClass('d-none');
    } 
    $(function() {
        $("#assignto").trigger('change');
        getHandyman(provider_id, booking_address_id)
    });
    $(document).on('change','#assignto', function() {
        if ($(this).val() == 'handyman') {
            getHandyman(user_id);
            $('#handyman_id').attr("required", true);
            $('#assign-to-handyman').removeClass('d-none');
        } else {
            $('#handyman_id').removeAttr('required');
            $('#assign-to-handyman').addClass('d-none');
            $("#handyman_id").val('').trigger('change');
        }
    });
    $('#provider_id').on('change', function() {
        getHandyman($(this).val());
    });

    function getHandyman(provider_id, booking_id = "") {
        var get_handyman_list;
        var userType = $('.row').attr('attr-user');
        if (booking_id == '') {
            get_handyman_list = "{{ route('ajax-list', [ 'type' => 'handyman','provider_id' =>'']) }}" + provider_id;
        } else {
            get_handyman_list = "{{ route('ajax-list', [ 'type' => 'handyman','booking_id' => '', 'provider_id' =>'']) }}" + provider_id;
            //get_handyman_list = get_handyman_list.replace(':provider_id', provider_id);
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
</script>