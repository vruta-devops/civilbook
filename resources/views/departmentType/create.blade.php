<x-master-layout>
    <?php
    $auth_user = authSession();
    ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? trans('messages.list') }}</h5>
                            @if($auth_user->can('department list'))
                                <a href="{{ route('department-types.index') }}"
                                   class="float-right btn btn-sm btn-primary"><i
                                            class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($departmentTypeData,['method' => 'POST','route'=>'department-types.store', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'department'] ) }}
                        {{ Form::hidden('id') }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('name',trans('messages.name').' <span class="text-danger">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('name',old('name'),['placeholder' => trans('messages.name'),'class' =>'form-control','required']) }}
                                <small class="help-block with-errors text-danger"></small>
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
            (function ($) {
                "use strict";
                $(document).ready(function () {
                    var shift_type_id = $('#shift_type_id').val();

                    getSubCategory(shift_type_id);
                    $("#shift_type_id").on("select2:select", function (e) {
                        if (e.params.data.id === 0) {
                            $("#shift_type_id option").each(function () {
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

                    $('#shift_type_id').on("select2:unselect", function (e) {
                        const selectedOptions = $(this).val();
                        $('#shift_hours_id').empty();
                        getSubCategory(selectedOptions);
                    })
                    $("#shift_hours_id").on("select2:select", function (e) {
                        if (e.params.data.id == 0) {
                            $("#shift_hours_id option").each(function () {
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
                    $('#shift_hours_id').on("select2:unselect", function (e) {
                        const selectedOptions = $('#shift_type_id').val();
                        getSubCategory(selectedOptions);
                    })
                })

                function getSubCategory(shift_type_id) {
                    if (shift_type_id) {
                        var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'shift_hours_list','multiple_category'=>'yes','shift_type_id' =>'']) }}" + shift_type_id;
                        get_subcategory_list = get_subcategory_list.split('amp;').join('');

                        $.ajax({
                            url: get_subcategory_list,
                            success: function (result) {

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
