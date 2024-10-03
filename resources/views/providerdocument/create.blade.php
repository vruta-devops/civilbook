<x-master-layout>
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if($auth_user->can('providerdocument list'))
                                <a href="{{ route('providerdocument.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($providerDocument,['method' => 'POST','route'=>'providerdocument.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'provider_document'] ) }}
                            {{ Form::hidden('id') }}
                            <div class="row">
                                @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                <div class="form-group col-md-4">
                                    {{ Form::label('provider_id', __('messages.select_name',[ 'select' => __('messages.providers') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('provider_id', [optional($providerDocument->provider)->id => optional($providerDocument->provider)->display_name], optional($providerDocument->provider)->id, [
                                        'class' => 'select2js form-group providers',
                                         'id' => 'provider_id',
                                        'required',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.providers') ]),
                                        'data-ajax--url' => route('ajax-list', ['type' => 'provider']),
                                    ]) }}
                                </div>
                                @endif
                                    <div class="form-group col-md-4">
                                        {{ Form::label('service_id', __('messages.select_name',[ 'select' => __('messages.services') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                        <br/>
                                        {{ Form::select('service_id', [optional($providerDocument->service)->id => optional($providerDocument->service)->name], optional($providerDocument->service)->id, [
                                                'class' => 'select2js form-group service_id',
                                                'id' => 'service_id',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.services') ]),
                                            ]) }}
                                    </div>


                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.document') ]).' <span class="text-danger">* </span>',['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('certificate', [optional($providerDocument->certificate)->id => optional($providerDocument->certificate)->name], optional($providerDocument->certificate)->id, [
                                            'class' => 'select2js form-group document_id',
                                            'name' => 'document_id',
                                            'id' => 'document_id',
                                            'required',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.document') ]),
                                        ])
                                    }}
                                </div>

                                <div class="form-group col-md-4">
                                    <label class="form-control-label" for="provider_document">{{ __('messages.upload_document') }} <span class="text-danger" id="document_required"></span> </label>
                                    <div class="custom-file">
                                        <input type="file" id="provider_document" name="provider_document" class="custom-file-input" required>
                                        <label class="custom-file-label upload-label">{{  __('messages.choose_file',['file' =>  __('messages.document') ]) }}</label>
                                    </div>
                                    <!-- <span class="selected_file"></span> -->
                                </div>
                                @if(getMediaFileExit($providerDocument, 'provider_document'))
                                    <div class="col-md-2 mb-2">
                                        <?php
                                            $file_extention = config('constant.IMAGE_EXTENTIONS');
                                            $image = getSingleMedia($providerDocument, 'provider_document');

                                            $extention = in_array(strtolower(imageExtention($image)),$file_extention);
                                        ?>
                                        @if($extention)
                                                <img id="provider_document_preview" src="{{ $image }}" alt="#" class="attachment-image mt-1" >
                                            @else
                                                <img id="provider_document_preview" src="{{ asset('images/file.png') }}" class="attachment-file">
                                            @endif
                                        <a class="text-danger remove-file"
                                           href="{{ route('remove.file', ['id' => $providerDocument->id, 'type' => 'provider_document']) }}"
                                                data--submit="confirm_form"
                                                data--confirmation='true'
                                                data--ajax="true"
                                                title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.image") ]) }}'
                                                data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.image") ]) }}'
                                                data-message='{{ __("messages.remove_file_msg") }}'>
                                                <i class="ri-close-circle-line"></i>
                                            </a>
                                            <a href="{{ $image }}" class="d-block mt-2" download target="_blank"><i class="fas fa-download "></i> {{ __('messages.download') }}</a>
                                    </div>
                                @endif
                            </div>
                            {{ Form::submit( trans('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
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
                $(document).ready(function () {
                    const provider = $('#provider_id');

                    const providerId = $('#provider_id').val() ? $('#provider_id').val() : {{auth()->user()->id}};

                    const serviceId = $('#service_id');
                    const docId = $('#document_id');
                    getServices(providerId)

                    provider.on('change', function () {
                        const id = $(this).val();
                        serviceId.empty();
                        getServices(id);
                    })

                    serviceId.on('change', function () {
                        const id = $(this).val();
                        docId.empty();
                        getDocument(id);
                    })

                    function getServices(providerId) {
                        let serviceList = "{{ route('ajax-list', [ 'type' => 'service', 'without_approval' => true,'provider_id' =>'']) }}" + providerId;
                        serviceList = serviceList.split('amp;').join('');
                        $.ajax({
                            url: serviceList,
                            success: function (result) {
                                serviceId.select2({
                                    width: '100%',
                                    placeholder: "{{ trans('messages.select_name',['select' => trans('messages.services')]) }}",
                                    data: result.results
                                });

                                serviceId.trigger('change')
                            }
                        });
                    }

                    function getDocument(serviceId) {
                        let documentList = "{{ route('ajax-list', [ 'type' => 'documents','service_id' =>'']) }}" + serviceId;
                        documentList = documentList.split('amp;').join('');
                        $.ajax({
                            url: documentList,
                            success: function (result) {
                                docId.select2({
                                    width: '100%',
                                    placeholder: "{{ trans('messages.select_name',['select' => trans('messages.services')]) }}",
                                    data: result
                                });
                            }
                        });
                    }
                    })
            })(jQuery);
        </script>
    @endsection
</x-master-layout>
