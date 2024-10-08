<x-master-layout>
    <div class="container-fluid">
        <div class="row">
        <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                                <a href="{{ route('provider.index') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @if($auth_user->can('provider list'))
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($providerdata,['method' => 'POST','route'=>'provider.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'provider'] ) }}
                            {{ Form::hidden('id') }}
                        {{ Form::hidden('filter_status', request('status')) }}
                            {{ Form::hidden('user_type','provider') }}
                            <div class="row">
                                <div class="form-group col-md-4">
                                    {{ Form::label('first_name',__('messages.full_name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('first_name',old('first_name'),['placeholder' => __('messages.full_name'),'class' =>'form-control','required']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('username',__('messages.username').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('username',old('username'),['placeholder' => __('messages.username'),'class' =>'form-control','required']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('email',__('messages.email').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::email('email',old('email'),['placeholder' => __('messages.email'),'class' =>'form-control','required']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                                @if (!isset($providerdata->id) || $providerdata->id == null)
                                    <div class="form-group col-md-4">
                                        {{ Form::label('password',__('messages.password').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                        {{ Form::password('password', ['class' => 'form-control', 'placeholder' => __('messages.password'), 'required']) }}
                                        <small class="help-block with-errors text-danger"></small>
                                    </div>
                                @endif
                                <div class="form-group col-md-4">
                                    {{ Form::label('designation',__('messages.designation'),['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('designation',old('designation'),['placeholder' => __('messages.designation'),'class' =>'form-control']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('providertype_id', __('messages.select_name',[ 'select' => __('messages.providertype') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('providertype_id', [optional($providerdata->providertype)->id => optional($providerdata->providertype)->name], optional($providerdata->providertype)->id, [
                                        'class' => 'select2js form-group providertype',
                                        'required',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.providertype') ]),
                                        'data-ajax--url' => route('ajax-list', ['type' => 'providertype']),
                                    ]) }}
                                </div>

                                @php
                                    $categories_id_provider = optional($providerdata->providerCategoryMapping)->pluck('category_id');
                                    $categories_provider = $providerdata->providerCategoryMapping->mapWithKeys(function ($item) {
                                        return [$item->category_id => optional($item->category)->name];
                                    });

                                    $sub_categories_id_provider = optional($providerdata->providerCategoryMapping)->pluck('sub_category_id');
                                    $sub_categories_provider = $providerdata->providerCategoryMapping->mapWithKeys(function ($item) {
                                        return [$item->sub_category_id => optional($item->subCategory)->name];
                                    });

                                    if(isset($providerdata->providerCategoryMapping[0]) && $providerdata->providerCategoryMapping[0]->is_category_all==1)
                                    {
                                        $categories_id_provider = 0;
                                        $categories_provider = [0 => 'All'];
                                    }

                                    if(isset($providerdata->providerCategoryMapping[0]) && $providerdata->providerCategoryMapping[0]->is_sub_category_all==1)
                                    {
                                        $sub_categories_id_provider = 0;
                                        $sub_categories_provider = [0 => 'All'];
                                    }

                                @endphp

                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.department') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    <br/>
                                    {{ Form::select('department_id', [optional($providerdata->department)->id => optional($providerdata->department)->name], optional($providerdata->department)->id, [
                                                'class' => 'select2js form-group department',
                                                'required',
                                                'id' => 'department_id',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.department') ]),
                                                'data-ajax--url' => route('ajax-list', ['type' => 'department', 'is_all_option' => 'no']),
                                            ]) }}

                                </div>

                                <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.category') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                    {{ Form::select('category_id[]', $categories_provider, $categories_id_provider, [
                                                'class' => 'select2js form-group category_id',
                                                'required',
                                                'id' => 'category_id',
                                                'multiple' => 'multiple',
                                                'allowClear' => 'true',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.category') ]),

                                            ]) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('subcategory_id', __('messages.select_name',[ 'select' => __('messages.subcategory') ]),['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('subcategory_id[]', $sub_categories_provider, $sub_categories_id_provider, [
                                            'class' => 'select2js form-group subcategory_id',
                                            'id' => 'subcategory_id',
                                            'multiple' => 'multiple',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.subcategory') ]),
                                        ]) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.badge') ]),['class'=>'form-control-label'],false) }}
                                    <br/>
                                    {{ Form::select('badge_provider_id', $badgedata->pluck('name', 'id'), $badge_provider_id, [
                                                'class' => 'select2js form-group',
                                                'id' => 'badge_provider_id',
                                                'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.badge') ])
                                            ]) }}

                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('country_id', __('messages.select_name',[ 'select' => __('messages.country') ]),['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('country_id', [optional($providerdata->country)->id => optional($providerdata->country)->name], optional($providerdata->country)->id, [
                                        'class' => 'select2js form-group country',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.country') ]),
                                        'data-ajax--url' => route('ajax-list', ['type' => 'country']),
                                    ]) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('state_id', __('messages.select_name',[ 'select' => __('messages.state') ]),['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('state_id', [], [
                                        'class' => 'select2js form-group state_id',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.state') ]),
                                    ]) }}
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('city_id', __('messages.select_name',[ 'select' => __('messages.city') ]),['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('city_id', [], old('city_id'), [
                                        'class' => 'select2js form-group city_id',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.city') ]),
                                    ]) }}
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.tax') ]),['class'=>'form-control-label'],false) }}
                                    <br />
                                    {{ Form::select('tax_id[]', [], old('tax_id'), [
                                        'class' => 'select2js form-group tax_id',
                                        'id' =>'tax_id',
                                        'multiple' => 'multiple',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.tax') ]),
                                    ]) }}

                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('preferred_distance',__('messages.preferred_distance'),['class'=>'form-control-label'], false ) }}
                                    {{ Form::select('preferred_location_distance', config('constant.PROVIDER_PRE_DISTANCE'),old('preferred_location_distance'),[ 'id' => 'preferred_location_distance' ,'class' =>'form-control select2js']) }}
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('contact_number',__('messages.contact_number').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                    {{ Form::text('contact_number',old('contact_number'),['placeholder' => __('messages.contact_number'),'class' =>'form-control','required']) }}
                                    <small class="help-block with-errors text-danger"></small>
                                </div>

                                <div class="form-group col-md-4">
                                    {{ Form::label('status',__('messages.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::select('status',['1' => __('messages.active') , '0' => __('messages.inactive') ],old('status'),[ 'class' =>'form-control select2js','required']) }}
                                </div>
                                <div class="form-group col-md-4">
                                    {{ Form::label('Grade',__('messages.grade').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                    {{ Form::select('provider_grade',['Grade A' => 'Grade A' , 'Grade B' => 'Grade B' ],old('provider_grade'),[ 'class' =>'form-control select2js','required']) }}
                                </div>
                                <div class="form-group col-md-4">
                                    <label class="form-control-label" for="profile_image">{{ __('messages.profile_image') }} </label>
                                    <div class="custom-file">
                                        <input type="file" name="profile_image" class="custom-file-input" accept="image/*">
                                        <label class="custom-file-label upload-label">{{  __('messages.choose_file',['file' =>  __('messages.profile_image') ]) }}</label>
                                    </div>
                                    <span class="selected_file"></span>
                                </div>

                                @if(getMediaFileExit($providerdata, 'profile_image'))
                                    <div class="col-md-2 mb-2">
                                        <img id="profile_image_preview" src="{{getSingleMedia($providerdata,'profile_image')}}" alt="#" class="attachment-image mt-1">
                                            <a class="text-danger remove-file" href="{{ route('remove.file', ['id' => $providerdata->id, 'type' => 'profile_image']) }}"
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
                                    {{ Form::label('address',__('messages.address'), ['class' => 'form-control-label']) }}
                                    {{ Form::textarea('address', null, ['class'=>"form-control textarea" , 'rows'=>3  , 'placeholder'=> __('messages.address') ]) }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <div class="custom-control custom-checkbox custom-control-inline">
                                        {{ Form::checkbox('is_featured', $providerdata->is_featured, null, ['class' => 'custom-control-input' , 'id' => 'is_featured' ]) }}
                                        <label class="custom-control-label" for="is_featured">{{ __('messages.set_as_featured')  }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            {{ Form::submit( __('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @php
        $data = $providerdata->providerTaxMapping->pluck('tax_id')->implode(',');
    @endphp
    @section('bottom_script')
        <script type="text/javascript">
            (function($) {
                "use strict";
                $(document).ready(function(){
                    var country_id =  "{{ isset($providerdata->country_id) ? $providerdata->country_id : 0 }}";
                    var state_id = "{{ isset($providerdata->state_id) ? $providerdata->state_id : 0 }}";
                    var city_id = "{{ isset($providerdata->city_id) ? $providerdata->city_id : 0 }}";

                    var provider_id =  "{{ isset($providerdata->id) ? $providerdata->id : '' }}";
                    var provider_tax_id =  "{{ isset($data) ? $data : [] }}";
                    var category_id = $('#category_id').val();
                    getTax(provider_id,provider_tax_id)
                    stateName(country_id , state_id);
                    getSubCategory(category_id);

                    $(document).on('change' , '#country_id' , function (){
                        var country = $(this).val();
                        $('#state_id').empty();
                        $('#city_id').empty();
                        stateName(country);
                    })
                    $(document).on('change' , '#state_id' , function (){
                        var state = $(this).val();
                        $('#city_id').empty();
                        cityName(state , city_id);
                    })
                    $("#category_id").on("select2:select", function(e) {
                        if (e.params.data.id===0) {
                            $("#category_id option").each(function() {
                                if ($(this).val() !== "0") {
                                    $(this).remove();
                                }
                            });
                            $("#category_id").trigger("change");
                        } else {
                            $("#category_id option[value='0']").remove();
                            $("#category_id").trigger("change");

                        }
                        const selectedOptions = $(this).val();
                        $('#subcategory_id').empty();
                        getSubCategory(selectedOptions);
                    })
                    $(document).on('change', '#department_id', function () {
                        var department_id = $(this).val();
                        $('#category_id').empty();
                        getCategory(department_id, category_id);
                        $('#subcategory_id').trigger('change');
                    })
                    $('#category_id').on("select2:unselect", function(e) {
                        const selectedOptions = $(this).val();
                        $('#subcategory_id').empty();
                        getSubCategory(selectedOptions);
                    })
                    $("#subcategory_id").on("select2:select", function (e) {
                        if (e.params.data.id == 0) {
                            $("#subcategory_id option").each(function() {
                                if ($(this).val() != 0) {
                                    $(this).remove();
                                }
                            });
                            $("#subcategory_id").trigger("change");
                        } else {
                            $("#subcategory_id option[value='0']").remove();
                            $("#subcategory_id").trigger("change");
                        }
                        const selectedOptions = $('#category_id').val();
                        getSubCategory(selectedOptions);
                    })
                    $('#subcategory_id').on("select2:unselect", function(e) {
                        const selectedOptions = $('#category_id').val();
                        getSubCategory(selectedOptions);
                    })
                })

                function getCategory(department_id, category_id = "") {
                    var get_category_list = "{{ route('ajax-list', [ 'type' => 'category','department' =>'']) }}" + department_id;
                    get_category_list = get_category_list.replace('amp;', '');

                    $.ajax({
                        url: get_category_list,
                        success: function (result) {
                            console.log("result================", result.results);
                            $('#category_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.category')]) }}",
                                data: result.results
                            });
                            if (category_id != "") {
                                $('#category_id').val(category_id).trigger('change');
                            }
                        }
                    });
                }

                function stateName(country , state ="" ){
                    var state_route = "{{ route('ajax-list', [ 'type' => 'state','country_id' =>'']) }}"+country;
                    state_route = state_route.replace('amp;','');

                    $.ajax({
                        url: state_route,
                        success: function(result){
                            $('#state_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.state')]) }}",
                                data: result.results
                            });
                            if(state != null){
                                $("#state_id").val(state).trigger('change');
                            }
                        }
                    });
                }
                function cityName(state , city =""){
                    var city_route = "{{ route('ajax-list', [ 'type' => 'city' ,'state_id' =>'']) }}"+state;
                    city_route = city_route.replace('amp;','');

                    $.ajax({
                        url: city_route,
                        success: function(result){
                            $('#city_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.city')]) }}",
                                data: result.results
                            });
                            if(city != null || city != 0){
                                $("#city_id").val(city).trigger('change');
                            }
                        }
                    });
                }

                function getTax(provider_id,provider_tax_id=""){
                    var provider_tax_route = "{{ route('ajax-list', [ 'type' => 'provider_tax','provider_id' =>'']) }}"+provider_id;
                    provider_tax_route = provider_tax_route.replace('amp;','');

                    $.ajax({
                        url: provider_tax_route,
                        success: function(result){
                            $('#tax_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.tax')]) }}",
                                data: result.results
                            });
                            if(provider_tax_id != ""){
                                $('#tax_id').val(provider_tax_id.split(',')).trigger('change');
                            }
                        }
                    });
                }

                function getSubCategory(category_id) {
                    var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'subcategory_list','multiple_category'=>'yes','category_id' =>'']) }}" + category_id;
                    get_subcategory_list = get_subcategory_list.split('amp;').join('');

                    $.ajax({
                        url: get_subcategory_list,
                        success: function(result) {

                            $('#subcategory_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                                data: result.results
                            });
                            //$('#subcategory_id').trigger('change');
                        }
                    });
                }
            })(jQuery);
        </script>
    @endsection
</x-master-layout>
