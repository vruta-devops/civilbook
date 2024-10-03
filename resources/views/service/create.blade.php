<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('service.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if($auth_user->can('service list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div id="response"></div>
                        {{ Form::model($servicedata,['method' => 'POST','route'=>'service.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'service'] ) }}
                        {{-- {{ Form::hidden('id') }} --}}

                        @if ($errors->any())
                            <div>
                                <div class="alert alert-danger" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            </div>
                        @endif
                        <input type="hidden" name="id" id="service_id" value={{ $servicedata->id }}>
                        <input type="hidden" name="shift_count" id="shift_count">
                        <input type="hidden" name="slots" id="slot_time">
                        {{ Form::hidden('filter_status', request('status')) }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name',__('messages.name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('name',old('name'),['placeholder' => __('messages.name'),'class' =>'form-control','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.service_type') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                <select class="form-control select2js admin_service_type" name="admin_service_type" data-placeholder="Select Service Type">
                                    <option value="self" {{ $servicedata->admin_service_type == 'self' ? 'selected' : '' }}>
                                        Self
                                    </option>
                                    @if(auth()->user()->user_type=='admin')
                                        <option value="common" {{ $servicedata->admin_service_type == 'common' ? 'selected' : '' }}>Common</option>
                                    @endif
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.department') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('department_id', [optional($servicedata->department)->id => optional($servicedata->department)->name], optional($servicedata->department)->id, [
                                            'class' => 'form-control select2js form-group department',
                                            'required',
                                            'id' => 'department_id',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.department') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'department', 'is_all_option' => 'no']),
                                        ]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.category') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />

                                {{ Form::select('category_id', [optional($servicedata->category)->id => optional($servicedata->category)->name], optional($servicedata->category)->id, [
                                        'class' => 'form-control select2js form-group category_id',
                                        'id' => 'category_id',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.category') ]),
                                    ]) }}

                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('subcategory_id', __('messages.select_name',[ 'select' => __('messages.subcategory') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                {{ Form::select('subcategory_id', [optional($servicedata->subcategory)->id => optional($servicedata->subcategory)->name], optional($servicedata->subcategory)->id, [
                                        'class' => 'form-control select2js form-group subcategory_id',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.subcategory') ]),
                                    ]) }}
                            </div>

                            @php
                                $assigned_service_providers = $servicedata->serviceProviderMapping->mapWithKeys(function ($item) {
                                return [$item->provider_id => optional($item->providers)->display_name];
                                });

                                $provider_address = $servicedata->providerServiceAddress->mapWithKeys(function ($item) {
                                return [$item->provider_address_id => optional($item->providerAddressMapping)->address];
                                });
                            @endphp

                            @if(auth()->user()->hasAnyRole(['admin','demo_admin']))
                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.provider') ]).' <span class="text-danger provider-required">*</span>',['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('provider_id[]', $assigned_service_providers , optional($servicedata->serviceProviderMapping)->pluck('provider_id'), [
                                                'class' => 'select2js form-group',
                                                'id' => 'provider_id',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider') ]),
                                            ]) }}
                                </div>
                            @endif
                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.provider_address') ]).' <span class="text-danger provider-required">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                {{ Form::select('provider_address_id[]', $provider_address, optional($servicedata->providerServiceAddress)->pluck('provider_address_id'), [
                                        'class' => 'form-control select2js form-group provider_address_id',
                                        'id' =>'provider_address_id',
                                        'multiple' => 'multiple',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.provider_address') ]),
                                    ]) }}
                                    <small class="help-block with-errors text-danger"></small>
                                <a href="{{ route('provideraddress.create') }}" class=""><i class="fa fa-plus-circle mt-2"></i> {{ trans('messages.add_form_title',['form' => trans('messages.provider_address')  ]) }}</a>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="service_attachment">{{ __('messages.image') }} </label>
                                <div class="custom-file">
                                    <input type="file" name="service_attachment[]" class="custom-file-input" data-file-error="{{ __('messages.files_not_allowed') }}" multiple>
                                    <label class="custom-file-label upload-label">{{ __('messages.choose_file',['file' =>  __('messages.attachments') ]) }}</label>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('area',__('messages.area').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('area',old('area'),['placeholder' => __('messages.area'),'class' =>'form-control','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="div_business_name">
                                {{ Form::label('business_name',__('messages.business_name'),['class'=>'form-control-label'], false ) }}
                                {{ Form::text('business_name',old('business_name'),['placeholder' => __('messages.business_name'),'class' =>'form-control']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="div_designation">
                                {{ Form::label('designation',__('messages.designation'),['class'=>'form-control-label'], false ) }}
                                {{ Form::text('designation',old('designation'),['placeholder' => __('messages.designation'),'class' =>'form-control']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="preferred_distance_div">
                                {{ Form::label('preferred_distance',__('messages.preferred_distance').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::select('preferred_distance',$preDiff,old('preferred_distance'),[ 'id' => 'preferred_distance' ,'class' =>'form-control select2js']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="plot_area_div">
                                {{ Form::label('plot_area',__('messages.plot_area').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('plot_area',old('plot_area'),['placeholder' => __('messages.plot_area'),'class' =>'form-control','id' =>'plot_area']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="is_interest_rates_div">
                                {{ Form::label('interest_rate',__('messages.interest_rate').' % <span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::number('interest_rate',old('interest_rate'),['min' => 1, 'max' => 99, 'step' => 'any' ,'placeholder' => __('messages.interest_rate'),'class' =>'form-control', 'id' => 'interest_rate']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="is_loan_process_div">
                                {{ Form::label('loan_process',__('messages.loan_process').' % <span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::number('loan_process',old('loan_process'),['min' => 1, 'max' => 99, 'step' => 'any', 'placeholder' => __('messages.loan_process'),'class' =>'form-control', 'id' => 'loan_process']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>

                        <div class="row service_attachment_div">
                            <div class="col-md-12">
                                @if(getMediaFileExit($servicedata, 'service_attachment'))
                                    @php
                                        $attchments = $servicedata->getMedia('service_attachment');
                                        $file_extention = config('constant.IMAGE_EXTENTIONS');
                                    @endphp
                                    <div class="border-left-2">
                                        <p class="ml-2"><b>{{ __('messages.attached_files') }}</b></p>
                                        <div class="ml-2 my-3">
                                            <div class="row">
                                                @foreach($attchments as $attchment )
                                                        <?php
                                                        $extention = in_array(strtolower(imageExtention($attchment->getFullUrl())), $file_extention);
                                                        ?>

                                                    <div class="col-md-2 pr-10 text-center galary file-gallary-{{$servicedata->id}}" data-gallery=".file-gallary-{{$servicedata->id}}" id="service_attachment_preview_{{$attchment->id}}">
                                                        @if($extention)
                                                            <a id="attachment_files" href="{{ $attchment->getFullUrl() }}" class="list-group-item-action attachment-list" target="_blank">
                                                                <img src="{{ $attchment->getFullUrl() }}" class="attachment-image" alt="">
                                                            </a>
                                                        @else
                                                            <a id="attachment_files" class="video list-group-item-action attachment-list" href="{{ $attchment->getFullUrl() }}">
                                                                <img src="{{ asset('images/file.png') }}" class="attachment-file">
                                                            </a>
                                                        @endif
                                                        <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $attchment->id, 'type' => 'service_attachment']) }}" data--submit="confirm_form" data--confirmation='true' data--ajax="true" data-toggle="tooltip" title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}' data-title='{{ __("messages.remove_file_title" , ["name" =>  __("messages.attachments") ] ) }}' data-message='{{ __("messages.remove_file_msg") }}'>
                                                            <i class="ri-close-circle-line"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4 d-none" id="is_experience_div">
                                {{ Form::label('is_experience',__('messages.is_experience').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('experience',old('experience'),['placeholder' => __('messages.is_experience'),'class' =>'form-control', 'id' => 'is_experience']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="is_expected_salary_div">
                                {{ Form::label('is_expected_salary',__('messages.is_expected_salary').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('expected_salary',old('expected_salary'),['placeholder' => __('messages.is_expected_salary'),'class' =>'form-control', 'id' => 'expected_salary']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="is_relocate_div">
                                {{ Form::label('is_relocate',__('messages.is_relocate').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('willing_to_relocate',old('willing_to_relocate'),['placeholder' => __('messages.is_relocate'),'class' =>'form-control', 'id' => 'is_relocate']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="is_used_travelling_div">
                                {{ Form::label('is_used_travelling',__('messages.is_used_travelling').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('user_for_travel',old('user_for_travel'),['placeholder' => __('messages.is_used_travelling'),'class' =>'form-control', 'id' => 'is_used_travelling']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="is_notice_joining_div">
                                {{ Form::label('is_notice_joining',__('messages.is_notice_joining').'<span class="text-danger custom-required d-none"> *</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('notice_period',old('notice_period'),['placeholder' => __('messages.is_notice_joining'),'class' =>'form-control', 'id' => 'is_notice_joining']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.service_add_type',[ 'select' => __('messages.service_add_type') ]),['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('type_id', [optional($servicedata->departmentType)->id => optional($servicedata->departmentType)->name], optional($servicedata->departmentType)->id, [
                                    'class' => 'select2js form-group',
                                    'id' => 'type_ids',
                                    'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.service_add_type') ]),
                                ]) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('status',__('messages.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',['1' => __('messages.active') , '0' => __('messages.inactive') ],old('status'),[ 'class' =>'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('start_date',__('messages.start_date').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::date('start_date',old('start_date'),['placeholder' => __('messages.start_date'),'class' =>'form-control min-datepicker','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('end_date',__('messages.end_date').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::text('end_date',old('end_date'),['placeholder' => __('messages.end_date'),'class' =>'form-control min-datepicker','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('material_unit',__('messages.material_unit'),['class'=>'form-control-label'],false) }}
                                {{ Form::select('material_unit_id',[optional($servicedata->materialUnit)->id => optional($servicedata->materialUnit)->name], optional($servicedata->materialUnit)->id, [ 'class' =>'form-control select2js' ,'id'=>'material_unit', 'data-placeholder' => __('messages.material_unit')]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 shift_div">
                                {{ Form::label('shift_type_id',__('messages.shift_type'),['class'=>'form-control-label'],false) }}
                                {{ Form::select('shift_type_id',[optional($servicedata->shiftType)->id => optional($servicedata->shiftType)->name], optional($servicedata->shiftType)->id, [ 'class' =>'form-control select2js' ,'id'=>'shift_type', 'data-placeholder' => __('messages.shift_type')]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 shift_div">
                                {{ Form::label('shift_hour_id',__('messages.shift_hours'),['class'=>'form-control-label'],false) }}
                                {{ Form::select('shift_hour_id',[optional($servicedata->shiftHour)->id => optional($servicedata->shiftHour)->hours_from .' - '. optional($servicedata->shiftHour)->hours_to], optional($servicedata->shiftHour)->id, [ 'class' =>'form-control select2js','id'=>'shift_type_hours', 'data-placeholder' => __('messages.shift_hours')]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-4 <?=$servicedata->type_id == 1 ? 'd-none' : '';?>" id="div_service_type">
                                {{ Form::label('price_type_id',__('messages.price_type'), ['class'=>'form-control-label'],false) }}
                                {{ Form::select('price_type_id',[optional($servicedata->priceType)->id => optional($servicedata->priceType)->name], optional($servicedata->priceType)->id, [ 'class' =>'form-control select2js', 'id'=>'price_type']) }}
                            </div>
                            <div class="form-group col-md-4" id="price_div">
                                {{ Form::label('price',__('messages.price'),['class'=>'form-control-label'],false) }}
                                {{ Form::number('price',old('price'), [ 'min' => 1, 'step' => 'any' , 'placeholder' => __('messages.price'),'class' =>'form-control', 'id' => 'price' ]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4" id="div_tax">
                                {{ Form::label('tax',__('messages.tax').' %', ['class' => 'form-control-label']) }}
                                {{ Form::number('tax',$servicedata->tax, [ 'min' => 0, 'max' => 99, 'step' => 'any' , 'id' =>'tax', 'placeholder' => __('messages.tax'), 'class' =>'form-control']) }}
                                <small id="discount_error" class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4 d-none" id="div_discount">
                                {{ Form::label('discount',__('messages.discount').' %', ['class' => 'form-control-label']) }}
                                {{ Form::number('discount',$servicedata->discount, [ 'min' => 0, 'max' => 99, 'step' => 'any' , 'id' =>'discount', 'placeholder' => __('messages.discount'), 'class' =>'form-control']) }}
                                <small id="discount_error" class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4 d-none" id="div_advancePay">
                                {{ Form::label('advance_payment_amount',__('messages.advance_payment_amount').' %', ['class' => 'form-control-label']) }}
                                {{ Form::number('advance_payment_amount',$servicedata->advance_payment_amount, [ 'min' => 0, 'max' => 99, 'step' => 'any' , 'id' =>'advance_payment_amount', 'placeholder' => __('messages.discount'), 'class' =>'form-control']) }}
                                <small id="discount_error" class="help-block with-errors text-danger"></small>
                            </div>
                        </div>

                        <div class="row d-none" id="div_site_visit">
                            <div class="form-group col-md-4">
                                {{ Form::label('site_visit',__('messages.site_visit').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('site_visit',[__('messages.site_visit_free') => __('messages.site_visit_free') , __('messages.site_visit_charged') => __('messages.site_visit_charged') , __('messages.site_visit_remote') => __('messages.site_visit_remote')],old('site_visit'),[ 'class' =>'form-control select2js','required']) }}
                            </div>
                            <div class="form-group col-md-4" id="price_div">
                                {{ Form::label('charged_price',__('messages.charged_price'),['class'=>'form-control-label'],false) }}
                                {{ Form::number('charged_price',$servicedata->charged_price ?? '',['min' => 1, 'step' => 'any' , 'placeholder' => __('messages.charged_price'),'class' =>'form-control', 'id' => 'charged_price' ] ) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-12">
                                {{ Form::label('description',__('messages.description'), ['class' => 'form-control-label']) }}
                                {{ Form::textarea('description', null, ['class'=>"form-control textarea" , 'rows'=>3  , 'placeholder'=> __('messages.description') ]) }}
                            </div>
                            <div class="form-group col-md-3">
                                <div class="custom-control custom-switch">
                                    {{ Form::checkbox('is_slot', $servicedata->is_slot, null, ['class' => 'custom-control-input', 'id' => 'is_slot' ]) }}
                                    <label class="custom-control-label"
                                           for="is_slot">{{ __('messages.slot') }}</label>
                                </div>
                            </div>
                            <div class="form-group col-md-3">
                                <div class="custom-control  custom-switch">
                                    {{ Form::checkbox('is_featured', $servicedata->is_featured, null, ['class' => 'custom-control-input' , 'id' => 'is_featured' ]) }}
                                    <label class="custom-control-label" for="is_featured">{{ __('messages.set_as_featured')  }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-3" id="is_enable_advance">
                                <div class="custom-control  custom-switch">
                                    {{ Form::checkbox('is_enable_advance_payment', $servicedata->is_enable_advance_payment , null, ['class' => 'custom-control-input' , 'id' => 'is_enable_advance_payment' ]) }}
                                    <label class="custom-control-label" for="is_enable_advance_payment">{{ __('messages.enable_advanced_payment')  }}
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-md-3" id="amount">
                                {{ Form::label('advance_payment_amount',__('messages.advance_payment_amount').' (%)',['class'=>'form-control-label'], false ) }}
                                {{ Form::number('advance_payment_amount',old('advance_payment_amount'),['placeholder' => __('messages.amount'),'class' =>'form-control','min' => '1', 'max' => '99']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>
                        <div class="row <?=$servicedata->is_slot == 1 ? '' : 'd-none';?>" id="time_slot_div">

                        </div>

                        @if($servicedata->admin_service_type != 'common' || auth()->user()->hasAnyRole(['admin','demo_admin']))
                        {{ Form::submit( __('messages.save'), ['class'=>'btn btn-md btn-primary float-right','id' => 'service-btn']) }}
                        @endif
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $data = $servicedata->providerServiceAddress->pluck('provider_address_id')->implode(',');
    @endphp
    @section('bottom_script')
        <script type="text/javascript">
            var discountInput = document.getElementById('discount');
            var discountError = document.getElementById('discount-error');

            discountInput.addEventListener('input', function() {
                var discountValue = parseFloat(discountInput.value);
                if (isNaN(discountValue) || discountValue < 0 || discountValue > 99) {
                    discountError.textContent = "{{ __('Discount value should be between 0 to 99') }}";
                } else {
                    discountError.textContent = "";
                }
            });
            var enable_advanced_payment = $("input[name='is_enable_advance_payment']").prop('checked');
            checkEnablePayment(enable_advanced_payment);

            var price_type = $("#price_type").val();
            enableAdvancePayment(price_type);

            $("#is_enable_advance_payment").change(function() {
                value = $(this).prop('checked') == true ? true : false;
                checkEnablePayment(value)
            })

            // check value then show hide lable
            function checkEnablePayment(value) {
                if (value == true) {
                    $("#amount").removeClass('d-none');
                } else {
                    $("#amount").addClass('d-none');
                }
            }

            function enableAdvancePayment(type) {
                if (type == 'fixed') {
                    $("#is_enable_advance").removeClass('d-none');
                } else {
                    $("#is_enable_advance").addClass('d-none');
                }
            }
            function getTimeSlot() {
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });
                var time_route = "{{ route('layout_page', [ 'page' => 'time_slot_child']) }}";
                var time_route_format = time_route.replace('amp;', '');

                $.ajax({
                    "type": "POST",
                    url: time_route_format,
                    data: {'service_id': $('#service_id').val()},
                    success: function(data) {
                        $('#time_slot_div').html(data);
                    }
                });
            }

            (function($) {
                "use strict";
                $(document).ready(function() {

                    // $('form[data-toggle="validator"]').on('submit', function (e) {

                    //     window.setTimeout(function () {
                    //         var errors = $('.has-error');
                    //          // Custom validation logic
                    //         var customValidationFailed = false;

                    //         // Example custom validation: Check if a specific input is empty
                    //         var specificInput = $('#specificInputId');
                    //         if (specificInput.val().trim() === '') {
                    //             specificInput.closest('.form-group').addClass('has-error');
                    //             customValidationFailed = true;
                    //         }

                    //         if (errors.length) {
                    //             $('html, body').animate({ scrollTop: "0" }, 500);
                    //             e.preventDefault()
                    //         }
                    //     }, 0);
                    // });

                     getTimeSlot();
                    $('#service-btn').on('click', function (e) {

                        var selectedSlots = [];
                        var selectedSlotsByDay = {};

                        $('.slot-link.active').each(function () {
                            var day = $(this).data('day');
                            var slot = $(this).data('slot');

                            if (!(day in selectedSlotsByDay)) {
                                selectedSlotsByDay[day] = [];
                            }

                            selectedSlotsByDay[day].push(slot);
                        });
                        for (var day in selectedSlotsByDay) {
                            selectedSlots.push({
                                day: day,
                                slot: selectedSlotsByDay[day]
                            });
                        }
                        $('#slot_time').val(JSON.stringify(selectedSlots));
                        var form = document.getElementById('service');
                        var formData = new FormData(form);
                        formData.append('slots',JSON.stringify(selectedSlots));

                        var $form = $('#service');
                            $form.validator('validate');
                            var isValid = !$form.find('.has-error').length;

                            if (isValid) {

                                var csrfToken = $('meta[name="csrf-token"]').attr('content');

                                var form_route = "{{ route('service.store') }}";
                                var form_route_format = form_route.replace('amp;', '');
                                $('#service').submit();

                            // console.log(form_route_format);
                            // console.log($form.serialize());
                            // $.ajaxSetup({
                            //         headers: {
                            //             'X-CSRF-TOKEN': csrfToken
                            //         }
                            //     });
                            //     $.ajax({
                            //         "type": "POST",
                            //         url: form_route_format,
                            //         data: formData,
                            //         success: function(data) {
                            //             console.log(data);
                            //         }
                            //     });

                            }
                            else{
                                // alert('else');
                            }

                        /*window.setTimeout(function () {

                            var errors = $('.has-error')
                            if (errors.length) {
                                $('html, body').animate({ scrollTop: "0" }, 500);
                                e.preventDefault()
                            }
                            else{
                                $('#service').submit();
                            }
                        }, 0);*/




                    });

                    $(function() {
                        // $('#service').get(0).reset();
                        var price_type = $("#price_type").val();
                        priceformat(price_type);


                        var site_visit = $("#site_visit").val();
                        priceformatsite(site_visit);
                    });
                    let provider_id = "{{ isset($servicedata->provider_id) ? $servicedata->provider_id : '' }}";
                    var provider_address_id = "{{ isset($data) ? $data : [] }}";

                    var category_id = "{{ isset($servicedata->category_id) ? $servicedata->category_id : '' }}";
                    var subcategory_id = "{{ isset($servicedata->subcategory_id) ? $servicedata->subcategory_id : '' }}";

                    var admin_service_type = "{{ isset($servicedata->admin_service_type) ? $servicedata->admin_service_type : 'self' }}";

                    getSubCategory(category_id, subcategory_id)

                    adminServiceType(admin_service_type)
                    var authid = {{auth()->user()->id}};

                    providerAddress(authid, provider_address_id);

                    $(document).on('change', '#is_slot', function() {
                        if ($(this).is(':checked')) {
                            $('#time_slot_div').removeClass('d-none');
                        }
                        else{
                            $('#time_slot_div').addClass('d-none');
                        }

                    });
                    $(document).on('change', '#subcategory_id', function() {
                        var sub_category_id = $(this).val();
                        // Get the query string from the current URL
                        const queryString = window.location.search;

                        // Parse the query string to extract the parameters
                        const urlParams = new URLSearchParams(queryString);

                        // Check if the 'id' parameter is present
                        if (!urlParams.has('id'))
                            $('#provider_id').empty()

                        providers($('#category_id').val(), sub_category_id);
                        if(sub_category_id == 74){
                            getServiceType('', sub_category_id);
                        }
                    })

                    $(document).on('change', '#provider_id', function() {
                        var provider_id = $(this).val();
                        $('#provider_address_id').empty();
                        providerAddress(provider_id, provider_address_id);
                    })
                    $(document).on('change', '#department_id', function () {
                        var department_id = $(this).val();
                        $('#category_id').empty();
                        $('#price_type').empty();
                        $('#shift_type').empty();
                        $('#shift_type_hours').empty();
                        $('#type_ids').empty();
                        $('#material_unit').empty();

                        getCategory(department_id, category_id);
                        getPriceType(department_id);
                        getShiftType(department_id);
                        getServiceType(department_id);
                        getMaterialUnit(department_id);
                        $('#subcategory_id').trigger('change');
                    })

                    $(document).on('change', '#category_id', function() {
                        var category_id = $(this).val();
                        var department_id = $('#department_id').val();
                        $('#subcategory_id').empty();
                        getSubCategory(category_id, subcategory_id);
                        $('#subcategory_id').trigger('change');
                    })

                    $(document).on('change', '#price_type', function() {
                        var price_type = $(this).val();
                        $(this).val(price_type);
                        if (price_type == 'free') {
                            $('#price').val('');
                            $('#discount').val('');
                        } else {
                            $('#price').val('');
                            $('#discount').val('');
                        }

                        priceformat(price_type);
                        enableAdvancePayment(price_type)
                    })

                    $(document).on('change', '#site_visit', function() {
                        var site_visit = $(this).val();
                        $(this).val(site_visit);
                        if (site_visit == 'Free') {
                            $('#charged_price').val('');
                        } else {
                            $('#charged_price').val('');
                        }
                        priceformatsite(site_visit);
                    })

                    $(".admin_service_type").change(function() {
                        if ($(this).val() == 'self') {
                            $('#provider_id').removeAttr('multiple');
                        } else {
                            $('#provider_id').attr('multiple', 'multiple');
                        }
                        $("#provider_id").val('').trigger('change');
                        adminServiceType($(this).val())
                    })

                    $('.galary').each(function(index, value) {
                        let galleryClass = $(value).attr('data-gallery');
                        $(galleryClass).magnificPopup({
                            delegate: 'a#attachment_files',
                            type: 'image',
                            gallery: {
                                enabled: true,
                                navigateByImgClick: true,
                                preload: [0, 1] // Will preload 0 - before current, and 1 after the current image
                            },
                            callbacks: {
                                elementParse: function(item) {
                                    if (item.el[0].className.includes('video')) {
                                        item.type = 'iframe',
                                            item.iframe = {
                                                markup: '<div class="mfp-iframe-scaler">' +
                                                    '<div class="mfp-close"></div>' +
                                                    '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>' +
                                                    '<div class="mfp-title">Some caption</div>' +
                                                    '</div>'
                                            }
                                    } else {
                                        item.type = 'image',
                                            item.tLoading = 'Loading image #%curr%...',
                                            item.mainClass = 'mfp-img-mobile',
                                            item.image = {
                                                tError: '<a href="%url%">The image #%curr%</a> could not be loaded.'
                                            }
                                    }
                                }
                            }
                        })
                    })
                    $(document).on('change', '#type_ids', function() {
                        if($(this).val() == 1){
                            $('#div_service_type').addClass('d-none');
                            if($('#shift_count').val() > 0){
                                $('.shift_div').removeClass('d-none');
                            }
                            else{
                                $('.shift_div').addClass('d-none');
                            }
                        }
                        else{
                            $('#div_service_type').removeClass('d-none');
                            $('.shift_div').addClass('d-none');
                        }
                    });
                })

                $(document).on('change', '#shift_type', function () {
                    var shift_type_id = $(this).val();

                    $('#shift_type_hours').empty();

                    var department_id = $('#department_id').val();
                    getShiftTypeHours(department_id, shift_type_id);
                    $('#shift_type_hours').trigger('change');
                })

                function providers(categoryId, subCategoryId) {
                    const url = @json(url('ajax-list'));;

                    var providers_route = url + "?type=provider&category_id=" + categoryId + "&subcategory_id=" + subCategoryId;

                    providers_route = providers_route.replace('amp;', '');
                    var provider_id_exist = $('#provider_id').val();
                    $.ajax({
                        url: providers_route,
                        success: function(result) {
                            $('#provider_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.provider_address')]) }}",
                                data: result.results
                            });
                            if(provider_id){
                                $('#provider_id').val(provider_id_exist).trigger('change');
                            }
                            else{
                                $('#provider_id').val('').trigger('change');
                            }
                        }
                    });
                }
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
                            if(provider_address_id){
                                $('#provider_address_id').val(provider_address_id).trigger('change');
                            }
                            else{
                                $('#provider_address_id').val('').trigger('change');
                            }
                            //$('#subcategory_id').val('').trigger('change');
                        }
                    });
                }

                function getCategory(department_id, category_id = "") {
                    var get_category_list = "{{ route('ajax-list', [  'type' => 'category', 'department' => '']) }}" + department_id+"&is_all_option=no";
                    get_category_list = get_category_list.replace('amp;', '');

                    $.ajax({
                        url: get_category_list,
                        success: function (result) {
                            $('#category_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.category')]) }}",
                                data: result.results
                            });

                            if (category_id != "") {
                                $('#category_id').val(category_id).trigger('change');
                            }
                            else{
                                $('#category_id').val('').trigger('change')
                            }
                        }

                    });
                }

                function getPriceType(department_id) {
                    var get_price_list = "{{ route('ajax-list', [ 'type' => 'pricetype','department' =>'']) }}" + department_id;
                    get_price_list = get_price_list.replace('amp;', '');

                    $.ajax({
                        url: get_price_list,
                        success: function (result) {
                            $('#price_type').select2({
                                allowClear: true,
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.price_type')]) }}",
                                data: result.results
                            });
                        }

                    });$('#price_type').val('').trigger('change');
                }

                function getServiceType(department_id = '', sub_category_id = '') {
                    $('#type_ids').empty();
                    var get_price_list = "{{ route('ajax-list', [ 'type' => 'servicetypebydept','department' =>'']) }}" + department_id+"&subcategory="+sub_category_id;
                    get_price_list = get_price_list.replace('amp;', '');

                    $.ajax({
                        url: get_price_list,
                        success: function (result) {
                            $('#type_ids').select2({
                                allowClear: true,
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.price_type')]) }}",
                                data: result.results
                            });
                            $('#type_ids').val(null).trigger('change');
                        }
                    });

                }

                function getMaterialUnit(department_id = '') {
                    $('#material_unit').empty();
                    var get_material_list = "{{ route('ajax-list', [ 'type' => 'servicematerialbydept','department' =>'']) }}" + department_id;
                    get_material_list = get_material_list.replace('amp;', '');

                    $.ajax({
                        url: get_material_list,
                        success: function (result) {
                            $('#material_unit').select2({
                                allowClear: true,
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.material_unit')]) }}",
                                data: result.results
                            });
                            $('#material_unit').val(null).trigger('change');
                        }
                    });

                }

                function getShiftType(department_id) {
                    var get_shift_list = "{{ route('ajax-list', [ 'type' => 'shifttype','department' =>'']) }}" + department_id;
                    get_shift_list = get_shift_list.replace('amp;', '');

                    $.ajax({
                        url: get_shift_list,
                        success: function (result) {
                            $('#shift_type').select2({
                                allowClear: true,
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.shift_type')]) }}",
                                data: result.results
                            });
                            $('#shift_type').val(null).trigger('change');
                        }

                    });
                }

                function getShiftTypeHours(department_id, shift_type_id) {
                    var get_shifthours_list = "{{ route('ajax-list', [ 'type' => 'shifttypehours','shift_type_id' =>'']) }}" + shift_type_id;
                    get_shifthours_list = get_shifthours_list.replace('amp;', '');

                    $.ajax({
                        url: get_shifthours_list,
                        success: function (result) {
                            $('#shift_type_hours').select2({
                                allowClear: true,
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.shift_hours')]) }}",
                                data: result.results
                            });
                            $('#shift_type_hours').val(null).trigger('change');
                        }
                    });
                }

                function getSubCategory(category_id, subcategory_id = "") {
                    var department_id = $('#department_id').val();

                    var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'subcategory_list','category_id' =>'']) }}" + category_id+"&is_all_option=no";
                    get_subcategory_list = get_subcategory_list.replace('amp;', '');

                    $.ajax({
                        url: get_subcategory_list,
                        success: function(result) {
                            $('#subcategory_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                                data: result.results
                            });
                            if (subcategory_id != "") {
                                $('#subcategory_id').val(subcategory_id).trigger('change');
                            }
                            else{
                                $('#subcategory_id').val('').trigger('change');
                            }
                        }
                    });

                    var get_department_by_id = "{{ route('ajax-list', [ 'type' => 'departmentbyid','department' =>'']) }}" + department_id;
                    get_department_by_id = get_department_by_id.replace('amp;', '');

                    $.ajax({
                        url: get_department_by_id,
                        success: function (result) {
                            var response = result.results;
                            if(typeof response[0] != 'undefined'){
                                if(response[0].is_site_visit == 1){
                                    $('#div_site_visit').removeClass('d-none');
                                }
                                else{
                                    $('#div_site_visit').addClass('d-none');
                                }
                                if(response[0].is_business_name == 1){
                                    $('#div_business_name').removeClass('d-none');
                                }
                                else{
                                    $('#div_business_name').addClass('d-none');
                                }
                                if(response[0].is_designation == 1){
                                    $('#div_designation').removeClass('d-none');
                                }
                                else{
                                    $('#div_designation').addClass('d-none');
                                }
                                if(response[0].is_discount_enabled == 1){
                                    $('#div_discount').removeClass('d-none');
                                }
                                else{
                                    $('#div_discount').addClass('d-none');
                                }
                                if(response[0].is_tax == 1){
                                    $('#div_tax').removeClass('d-none');
                                }
                                else{
                                    $('#div_tax').addClass('d-none');
                                }
                                if(response[0].is_advance_payment == 1){
                                    $('#is_enable_advance').removeClass('d-none');
                                }
                                else{
                                    $('#is_enable_advance').addClass('d-none');
                                }
                                if(response[0].department_shift_hours_mapping_count > 0){
                                    $('#shift_count').val(response[0].department_shift_hours_mapping_count);
                                }
                                else{
                                    $('#shift_count').val(response[0].department_shift_hours_mapping_count);
                                }

                                if(response[0].is_preferred == 1){
                                    $('#preferred_distance_div').find('span.custom-required').removeClass('d-none');
                                    $('#preferred_distance').attr("required", true);
                                    $('#preferred_distance_div').removeClass('d-none');
                                }
                                else{
                                    $('#preferred_distance_div').find('span.custom-required').addClass('d-none');
                                    $('#preferred_distance').removeAttr("required");
                                    $('#preferred_distance_div').removeClass('has-error has-danger');
                                    $('#preferred_distance_div').addClass('d-none');
                                }
                                if(response[0].is_plot_area == 1){
                                    $('#plot_area_div').find('span.custom-required').removeClass('d-none');
                                    $('#plot_area').attr("required", true);
                                    $('#plot_area_div').removeClass('d-none');
                                }
                                else{
                                    $('#plot_area_div').find('span.custom-required').addClass('d-none');
                                    $('#plot_area').removeAttr("required");
                                    $('#plot_area_div').removeClass('has-error has-danger');
                                    $('#plot_area_div').addClass('d-none');
                                }
                                if(response[0].is_interest_rates == 1){
                                    $('#is_interest_rates_div').find('span.custom-required').removeClass('d-none');
                                    $('#interest_rate').attr("required", true);
                                    $('#is_interest_rates_div').removeClass('d-none');

                                }
                                else{
                                    $('#is_interest_rates_div').find('span.custom-required').addClass('d-none');
                                    $('#interest_rate').removeAttr("required");
                                    $('#is_interest_rates_div').removeClass('has-error has-danger');
                                    $('#is_interest_rates_div').addClass('d-none');
                                }
                                if(response[0].is_loan_process == 1){
                                    $('#is_loan_process_div').find('span.custom-required').removeClass('d-none');
                                    $('#loan_process').attr("required", true);
                                    $('#is_loan_process_div').removeClass('d-none');
                                }
                                else{
                                    $('#is_loan_process_div').find('span.custom-required').addClass('d-none');
                                    $('#loan_process').removeAttr("required");
                                    $('#is_loan_process_div').removeClass('has-error has-danger');
                                    $('#is_loan_process_div').addClass('d-none');
                                }
                                if(response[0].is_experience == 1){
                                    $('#is_experience_div').find('span.custom-required').removeClass('d-none');
                                    $('#is_experience').attr("required", true);
                                    $('#is_experience_div').removeClass('d-none');
                                }
                                else{
                                    $('#is_experience_div').find('span.custom-required').addClass('d-none');
                                    $('#is_experience').removeAttr("required");
                                    $('#is_experience_div').removeClass('has-error has-danger');
                                    $('#is_experience_div').addClass('d-none');
                                }
                                if(response[0].is_expected_salary == 1){
                                    $('#is_expected_salary_div').find('span.custom-required').removeClass('d-none');
                                    $('#is_expected_salary').attr("required", true);
                                    $('#is_expected_salary_div').removeClass('d-none');
                                }
                                else{
                                    $('#is_expected_salary_div').find('span.custom-required').addClass('d-none');
                                    $('#is_expected_salary').removeAttr("required");
                                    $('#is_expected_salary_div').removeClass('has-error has-danger');
                                    $('#is_expected_salary_div').addClass('d-none');
                                }
                                if(response[0].is_relocate == 1){
                                    $('#is_relocate_div').find('span.custom-required').removeClass('d-none');
                                    $('#is_relocate').attr("required", true);
                                    $('#is_relocate_div').removeClass('d-none');
                                }
                                else{
                                    $('#is_relocate_div').find('span.custom-required').addClass('d-none');
                                    $('#is_relocate').removeAttr("required");
                                    $('#is_relocate_div').removeClass('has-error has-danger');
                                    $('#is_relocate_div').addClass('d-none');
                                }

                                if(response[0].is_used_travelling == 1){
                                    $('#is_used_travelling_div').find('span.custom-required').removeClass('d-none');
                                    $('#is_used_travelling').attr("required", true);
                                    $('#is_used_travelling_div').removeClass('d-none');
                                }
                                else{
                                    $('#is_used_travelling_div').find('span.custom-required').addClass('d-none');
                                    $('#is_used_travelling').removeAttr("required");
                                    $('#is_used_travelling_div').removeClass('has-error has-danger');
                                    $('#is_used_travelling_div').addClass('d-none');
                                }

                                if(response[0].is_notice_joining == 1){
                                    $('#is_notice_joining_div').find('span.custom-required').removeClass('d-none');
                                    $('#is_notice_joining').attr("required", true);
                                    $('#is_notice_joining_div').removeClass('d-none');
                                }
                                else{
                                    $('#is_notice_joining_div').find('span.custom-required').addClass('d-none');
                                    $('#is_notice_joining').removeAttr("required");
                                    $('#is_notice_joining_div').removeClass('has-error has-danger');
                                    $('#is_notice_joining_div').addClass('d-none');
                                }
                            }
                        }
                    });
                }

                function priceformat(value) {
                    if (value == 'free') {
                        $('#price').attr("readonly", true)
                        $('#discount').attr("readonly", true)
                    } else {
                        $('#price').attr("readonly", false)
                        $('#discount').attr("readonly", false)
                    }
                }
                function priceformatsite(value) {
                    if (value == 'Free') {
                        $('#charged_price').attr("readonly", true)
                    } else {
                        $('#charged_price').attr("readonly", false)
                    }
                }

                function adminServiceType(value) {
                    if (value == 'self') {
                        $('#provider_id').removeAttr('multiple').select2();
                        $('#provider_id').attr("required", true);
                        $('.provider-required').removeClass('d-none');
                    } else {
                        $('#provider_id').attr('multiple', 'multiple').select2();
                        $('#provider_id').removeAttr('required');
                        $('.provider-required').addClass('d-none');
                    }
                }
                const discountInput = document.querySelector('#discount');
                const discountError = document.querySelector('#discount_error');
                discountInput.addEventListener('input', function() {
                    if (this.value > 99) {
                        this.value = '';
                        discountError.innerText = 'Discount must be less than or equal to 99';
                    } else {
                        discountError.innerText = '';
                    }
                });
            })(jQuery);
        </script>
    @endsection
</x-master-layout>
