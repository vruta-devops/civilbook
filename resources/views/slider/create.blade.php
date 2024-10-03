<x-master-layout>
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            @if($auth_user->can('slider list'))
                            @endif
                                <a href="{{ route('slider.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($sliderdata,['method' => 'POST','route'=>'slider.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'slider'] ) }}
                            {{ Form::hidden('id') }}
                            {{ Form::hidden('type','service') }}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    {{ Form::label('title',__('messages.title').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('title',old('title'),['placeholder' => __('messages.title'),'class' =>'form-control','required']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                                @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                    <div class="form-group col-md-4">
                                        {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.provider') ]).' <span class="text-danger provider-required">*</span>',['class'=>'form-control-label'],false) }}
                                        <br />
                                        {{ Form::select('provider_id', [ optional($sliderdata->provider)->id => optional($sliderdata->provider)->display_name ], optional($sliderdata->provider)->id, [
                                            'class' => 'select2js form-group',
                                            'id' => 'provider_id',
                                            'required',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'provider']),
                                        ]) }}
                                    </div>
                                    <div class="form-group col-md-4">
                                        {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.provider_address') ]),['class'=>'form-control-label'],false) }}
                                        <br />
                                        {{ Form::select('provider_address_id', [ optional($sliderdata->providerAddress)->id => optional($sliderdata->providerAddress)->display_name ], optional($sliderdata->providerAddress)->id, [
                                        'class' => 'select2js form-group',
                                        'id' => 'provider_address_id',
                                        'required',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider_address') ]),
                                        ]) }}
                                    </div>

                                @endif

                                <div class="form-group col-md-4">
                                    {{ Form::label('type_id', __('messages.select_name',[ 'select' => __('messages.service') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('type_id', [optional($sliderdata->service)->id => optional($sliderdata->service)->name], optional($sliderdata->service)->id, [
                                            'class' => 'select2js form-group service',
                                            'required',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.service') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'service']),
                                        ])
                                    }}
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('status',__('messages.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::select('status',['1' => __('messages.active') , '0' => __('messages.inactive') ],old('status'),[ 'id' => 'status' ,'class' =>'form-control select2js','required']) }}
                                </div>

                                <div class="form-group col-md-6">
                                    <label class="form-control-label" for="slider_image">{{ __('messages.image') }} </label>
                                    <div class="custom-file">
                                        <input type="file" name="slider_image" class="custom-file-input" accept="image/*">
                                        <label class="custom-file-label upload-label">{{  __('messages.choose_file',['file' =>  __('messages.image') ]) }}</label>
                                    </div>
                                    <span class="selected_file"></span>
                                </div>

                                @if(getMediaFileExit($sliderdata, 'slider_image'))
                                    <div class="col-md-2 mb-2">
                                        <img id="slider_image_preview" src="{{getSingleMedia($sliderdata,'slider_image')}}" alt="#" class="attachment-image mt-1">
                                            <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $sliderdata->id, 'type' => 'slider_image']) }}"
                                                data--submit="confirm_form"
                                                data--confirmation='true'
                                                data--ajax="true"
                                                data-toggle="tooltip"
                                                title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.image") ]) }}'
                                                data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.image") ]) }}'
                                                data-message='{{ __("messages.remove_file_msg") }}'>
                                                <i class="ri-close-circle-line"></i>
                                            </a>
                                    </div>
                                @endif
                                <div class="form-group col-md-12">
                                    {{ Form::label('description',__('messages.description'), ['class' => 'form-control-label']) }}
                                    {{ Form::textarea('description', null, ['class'=>"form-control textarea" , 'rows'=>3  , 'placeholder'=> __('messages.description') ]) }}
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
        <script type="text/javascript">
            (function($) {
                "use strict";
                $(document).ready(function() {
                    var provider_id = "{{ isset($sliderdata->provider_id) ? $sliderdata->provider_id : '' }}";

                    $(document).on('change', '#provider_id', function() {
                        var provider_id = $(this).val();
                        $('#provider_address_id').empty();
                        providerAddress(provider_id, provider_address_id);
                    })
                })

                function providerAddress(provider_id, provider_address_id = "") {
                    var provider_address_route = "{{ route('ajax-list', [ 'type' => 'provider_address','provider_id' =>'']) }}" + provider_id;
                    provider_address_route = provider_address_route.replace('amp;', '');

                    $.ajax({
                        url: provider_address_route,
                        success: function(result) {
                            $('#provider_address_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.provider_address')]) }}",
                                data: result.results
                            });
                            //$('#subcategory_id').val('').trigger('change');
                        }
                    });
                }
            })(jQuery);
        </script>
    @endsection
</x-master-layout>
