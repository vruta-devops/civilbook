<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if($auth_user->can('department list'))
                                <a href="{{ route('department.index') }}" class="float-right btn btn-sm btn-primary"><i
                                            class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($departmentData,['method' => 'POST','route'=>'department.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'department'] ) }}
                        {{ Form::hidden('id') }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name',trans('messages.name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('name',old('name'),['placeholder' => trans('messages.name'),'class' =>'form-control','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                            <div class="form-group col-md-4">
                                <label class="form-control-label" for="department_image">{{ __('messages.image') }}
                                    <span class="text-danger">*</span></label>
                                <div class="custom-file">
                                    <input type="file" name="department_image" class="custom-file-input"
                                           onchange="preview()" accept="image/*">
                                    <label class="custom-file-label upload-label">{{ __('messages.choose_file',['file' =>  __('messages.image') ]) }}</label>
                                </div>
                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('status',trans('messages.status').' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                {{ Form::select('status',['1' => __('messages.active') , '0' => __('messages.inactive') ],old('status'),[ 'id' => 'role' ,'class' =>'form-control select2js','required']) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.shift_hours',[ 'select' => __('messages.shift_hours') ]),['class'=>'form-control-label'],false) }}
                                    <br/>
                                <select id="type" class="select2js form-group @error('shift_hours_id') is-invalid @enderror" name="shift_hours_id[]" multiple id="shift_hours_id"  data-placeholder="Select {{__('messages.shift_hours')}}">
                                <option></option>
                                    @foreach($shifTypetData as $type)
                                        <optgroup label="{{ $type->name }}">
                                            @foreach($type->shiftHours as $type)
                                                @if (!empty($type->hours_to ))
                                                    <option value="{{ $type->id }}" <?= in_array($type->id, $deptShiftHours) ? 'selected' : '' ?>>{{ $type->hours_from }}
                                                        - {{ $type->hours_to }}</option>
                                                @else
                                                    <option value="{{ $type->id }}" <?= in_array($type->id, $deptShiftHours) ? 'selected' : '' ?>>{{ $type->hours_from }}</option>
                                                @endif
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.price_type',[ 'select' => __('messages.price_type') ]),['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('price_types_id[]', ['' => ''] + $priceTypeData->pluck('name', 'id')->toArray() , $deptPriceType, [
                                            'class' => 'select2js form-group',
                                            'id' => 'price_type_id',
                                            'multiple' => 'multiple',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.price_type') ]),
                                        ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.material_unit',[ 'select' => __('messages.material_unit') ]),['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('material_unit_id[]', ['' => ''] + $materialUnitData->pluck('name', 'id')->toArray() , $deptMaterialType, [
                                            'class' => 'select2js form-group',
                                            'id' => 'material_unit_id',
                                            'multiple' => 'multiple',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.material_unit') ]),
                                        ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.service_type',[ 'select' => __('messages.service_type') ]),['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('type_ids[]', ['' => ''] + $departmentTypeData->pluck('name', 'id')->toArray() , $deptType, [
                                            'class' => 'select2js form-group',
                                            'id' => 'type_ids',
                                            'multiple' => 'multiple',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.service_type') ]),
                                        ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.required_certificates',[ 'select' => __('messages.required_certificates') ]),['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('required_certificates[]', ['' => ''] + $requiredDocuments->pluck('name', 'id')->toArray() , $requiredCertificates, [
                                            'class' => 'select2js form-group',
                                            'id' => 'required_certificates',
                                            'multiple' => 'multiple',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.required_certificates') ]),
                                        ]) }}
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.optional_certificates',[ 'select' => __('messages.optional_certificates') ]),['class'=>'form-control-label'],false) }}
                                <br/>
                                {{ Form::select('optional_certificates[]', ['' => ''] + $optionalDocuments->pluck('name', 'id')->toArray() , $optionalCertificates, [
                                            'class' => 'select2js form-group',
                                            'id' => 'optional_certificates',
                                            'multiple' => 'multiple',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.optional_certificates') ]),
                                        ]) }}
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_transport_option', $departmentData->is_transport_option, null, ['class' => 'custom-control-input' , 'id' => 'is_transport_option', 'name' => 'is_transport_option' ]) }}
                                    <label class="custom-control-label" for="is_transport_option">{{ __('messages.is_transport_option')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_experience', $departmentData->is_experience, null, ['class' => 'custom-control-input' , 'id' => 'is_experience', 'name' => 'is_experience' ]) }}
                                    <label class="custom-control-label" for="is_experience">{{ __('messages.is_experience')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_site_visit', $departmentData->is_site_visit, null, ['class' => 'custom-control-input' , 'id' => 'is_site_visit', 'name'=> 'is_site_visit' ]) }}
                                    <label class="custom-control-label"
                                           for="is_site_visit">{{ __('messages.is_site_visit')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_expected_salary', $departmentData->is_expected_salary, null, ['class' => 'custom-control-input' , 'id' => 'is_expected_salary', 'name' => 'is_expected_salary' ]) }}
                                    <label class="custom-control-label" for="is_expected_salary">{{ __('messages.is_expected_salary')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_relocate', $departmentData->is_relocate, null, ['class' => 'custom-control-input' , 'id' => 'is_relocate' , 'name' => 'is_relocate' ]) }}
                                    <label class="custom-control-label" for="is_relocate">{{ __('messages.is_relocate')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_used_travelling', $departmentData->is_used_travelling, null, ['class' => 'custom-control-input' , 'id' => 'is_used_travelling', 'name' => 'is_used_travelling' ]) }}
                                    <label class="custom-control-label" for="is_used_travelling">{{ __('messages.is_used_travelling')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_notice_joining', $departmentData->is_notice_joining, null, ['class' => 'custom-control-input' , 'id' => 'is_notice_joining', 'name' => 'is_notice_joining' ]) }}
                                    <label class="custom-control-label" for="is_notice_joining">{{ __('messages.is_notice_joining')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_business_name', $departmentData->is_business_name, null, ['class' => 'custom-control-input' , 'id' => 'is_business_name', 'name' => 'is_business_name' ]) }}
                                    <label class="custom-control-label" for="is_business_name">{{ __('messages.is_business_name')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_designation', $departmentData->is_designation, null, ['class' => 'custom-control-input' , 'id' => 'is_designation', 'name' => 'is_designation' ]) }}
                                    <label class="custom-control-label" for="is_designation">{{ __('messages.is_designation')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_preferred', $departmentData->is_preferred, null, ['class' => 'custom-control-input' , 'id' => 'is_preferred', 'name' => 'is_preferred' ]) }}
                                    <label class="custom-control-label" for="is_preferred">{{ __('messages.is_preferred')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_qualification', $departmentData->is_qualification, null, ['class' => 'custom-control-input' , 'id' => 'is_qualification', 'name' => 'is_qualification' ]) }}
                                    <label class="custom-control-label" for="is_qualification">{{ __('messages.is_qualification')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_plot_area', $departmentData->is_plot_area, null, ['class' => 'custom-control-input' , 'id' => 'is_plot_area', 'name' => 'is_plot_area' ]) }}
                                    <label class="custom-control-label" for="is_plot_area">{{ __('messages.is_plot_area')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_advance_payment', $departmentData->is_advance_payment, null, ['class' => 'custom-control-input' , 'id' => 'is_advance_payment', 'name' => 'is_advance_payment']) }}
                                    <label class="custom-control-label" for="is_advance_payment">{{ __('messages.is_advance_payment')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_tax', $departmentData->is_tax, null, ['class' => 'custom-control-input' , 'id' => 'is_tax', 'name' => 'is_tax' ]) }}
                                    <label class="custom-control-label" for="is_tax">{{ __('messages.is_tax')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_interest_rates', $departmentData->is_interest_rates, null, ['class' => 'custom-control-input' , 'id' => 'is_interest_rates', 'name' => 'is_interest_rates' ]) }}
                                    <label class="custom-control-label" for="is_interest_rates">{{ __('messages.is_interest_rates')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_loan_process', $departmentData->is_loan_process, null, ['class' => 'custom-control-input' , 'id' => 'is_loan_process', 'name' => 'is_loan_process' ]) }}
                                    <label class="custom-control-label" for="is_loan_process">{{ __('messages.is_loan_process')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_time_slots', $departmentData->is_time_slots, null, ['class' => 'custom-control-input' , 'id' => 'is_time_slots', 'name' => 'is_time_slots' ]) }}
                                    <label class="custom-control-label"
                                           for="is_time_slots">{{ __('messages.is_time_slots')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_discount_enabled', $departmentData->is_discount_enabled, null, ['class' => 'custom-control-input' , 'id' => 'is_discount_enabled', 'name' => 'is_discount_enabled' ]) }}
                                    <label class="custom-control-label"
                                           for="is_discount_enabled">{{ __('messages.is_discount_enabled')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_price_enabled', $departmentData->is_price_enabled, null, ['class' => 'custom-control-input' , 'id' => 'is_price_enabled', 'name' => 'is_price_enabled' ]) }}
                                    <label class="custom-control-label"
                                           for="is_price_enabled">{{ __('messages.is_price_enabled')  }}
                                    </label>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <div class="custom-control custom-checkbox custom-control-inline">
                                    {{ Form::checkbox('is_multiple_location', $departmentData->is_multiple_location, null, ['class' => 'custom-control-input' , 'id' => 'is_multiple_location', 'name' => 'is_multiple_location' ]) }}
                                    <label class="custom-control-label"
                                           for="is_multiple_location">{{ __('messages.is_multiple_location')  }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        {{ Form::submit( trans('messages.save'), ['class'=>'btn btn-md btn-primary float-right']) }}
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        function preview() {
            department_image_preview.src = URL.createObjectURL(event.target.files[0]);
        }
    </script>
@section('bottom_script')
    <script type="text/javascript">
        (function($) {
            "use strict";
            $(document).ready(function(){
                var shift_type_id = $('#shift_type_id').val();

                getSubCategory(shift_type_id);
                $("#shift_type_id").on("select2:select", function(e) {
                    if (e.params.data.id===0) {
                        $("#shift_type_id option").each(function() {
                            if ($(this).val() !== "0") {
                                $(this).remove();
                            }
                        });
                        $("#shift_type_id").trigger("change");
                    } else {
                        $("#shift_type_id option[value='0']").remove();
                        $("#shift_type_id").trigger("change");

                    }
                    const selectedOptions = $(this).val();
                    $('#shift_hours_id').empty();
                    getSubCategory(selectedOptions);
                })

                $('#shift_type_id').on("select2:unselect", function(e) {
                    const selectedOptions = $(this).val();
                    $('#shift_hours_id').empty();
                    getSubCategory(selectedOptions);
                })
                $("#shift_hours_id").on("select2:select", function (e) {
                    if (e.params.data.id == 0) {
                        $("#shift_hours_id option").each(function() {
                            if ($(this).val() != 0) {
                                $(this).remove();
                            }
                        });
                        $("#shift_hours_id").trigger("change");
                    } else {
                        $("#shift_hours_id option[value='0']").remove();
                        $("#shift_hours_id").trigger("change");
                    }
                    const selectedOptions = $('#shift_type_id').val();
                    getSubCategory(selectedOptions);
                })
                $('#shift_hours_id').on("select2:unselect", function(e) {
                    const selectedOptions = $('#shift_type_id').val();
                    getSubCategory(selectedOptions);
                })
            })

            function getSubCategory(shift_type_id) {
                if(shift_type_id){
                    var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'shift_hours_list','multiple_category'=>'yes','shift_type_id' =>'']) }}" + shift_type_id;
                    get_subcategory_list = get_subcategory_list.split('amp;').join('');

                    $.ajax({
                        url: get_subcategory_list,
                        success: function(result) {

                            $('#shift_hours_id').select2({
                                width: '100%',
                                placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                                data: result.results
                            });
                            //$('#shift_hours_id').trigger('change');
                        }
                    });
                }
            }
        })(jQuery);
    </script>
@endsection
</x-master-layout>
