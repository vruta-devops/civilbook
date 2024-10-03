<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-block card-stretch">
                    <div class="card-body p-0">
                        <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                            <h5 class="font-weight-bold">{{ $pageTitle ?? __('messages.list') }}</h5>
                            <a href="{{ route('service.audio-service-list') }}" class="float-right btn btn-sm btn-primary"><i class="fa fa-angle-double-left"></i> {{ __('messages.back') }}</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{ Form::model($audioservicedata,['method' => 'POST','route'=>'service.audio-service-post-save', 'enctype'=>'multipart/form-data', 'data-toggle'=>"validator" ,'id'=>'audio-service'] ) }}
                        {{ Form::hidden('id') }}
                        {{ Form::hidden('user_id', $audioservicedata->user_id) }}
                        <div class="row">
                            <div class="form-group col-md-4">
                                {{ Form::label('status', __('messages.select_name',[ 'select' => __('messages.status') ]).' <span class="text-danger">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                <select class="form-control select2js" name="status" data-placeholder="Select Status" id="status">
                                    <option value="0" {{ $audioservicedata->status == 0 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ $audioservicedata->status == 1 ? 'selected' : '' }}>Approve</option>
                                    <option value="2" {{ $audioservicedata->status == 2 ? 'selected' : '' }}>Reject</option>
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('title',__('messages.title').' <span class="text-danger" id="title-required">*</span>',['class'=>'form-control-label'], false ) }}
                                {{ Form::text('title','',['placeholder' => __('messages.title'),'class' =>'form-control','id' =>'title','required']) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>

                            <div class="form-group col-md-4">
                                {{ Form::label('name', __('messages.select_name',[ 'select' => __('messages.category') ]).' <span class="text-danger" id="category-required">*</span>',['class'=>'form-control-label'],false) }}
                                <br />
                                {{ Form::select('category_id[]', [], '', [
                                            'class' => 'select2js form-group category',
                                            'required',
                                            'id' => 'category_id',
                                            'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.category') ]),
                                            'data-ajax--url' => route('ajax-list', ['type' => 'category']),
                                        ]) }}

                            </div>
                            <div class="form-group col-md-4">
                                {{ Form::label('subcategory_id', __('messages.select_name',[ 'select' => __('messages.subcategory') ]),['class'=>'form-control-label'],false) }}
                                <br />
                                {{ Form::select('subcategory_id[]', [], '', [
                                        'class' => 'select2js form-group subcategory_id',
                                        'id' => 'subcategory_id',
                                        'data-placeholder' => __('messages.select_name',[ 'select' => __('messages.subcategory') ]),
                                    ]) }}
                            </div>
                            
                            <div class="form-group col-md-4">
                                {{ Form::label('price',__('messages.expected_price'),['class'=>'form-control-label'],false) }}
                                {{ Form::number('price','', [ 'min' => 1, 'step' => 'any' , 'placeholder' => __('messages.expected_price'),'class' =>'form-control', 'id' => 'price' ]) }}
                                <small class="help-block with-errors text-danger"></small>
                            </div>
                        </div>

                        <div class="row">
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
                $(function() {
                    getSubCategory('');
                    $('#status').trigger('change');
                });
                $(document).on('change', '#category_id', function() {
                    var category_id = $(this).val();
                    $('#subcategory_id').empty();
                    getSubCategory(category_id);
                })
                $(document).on('change', '#status', function() {
                    var status = $(this).val();
                    if (status == 1) {
                        $('#title').attr("required", true);
                        $('#title-required').removeClass('d-none');
                        $('#category_id').attr("required", true);
                        $('#category-required').removeClass('d-none');
                    } else {
                        $('#title').removeAttr("required");
                        $('#title-required').addClass('d-none');
                        $('#category_id').removeAttr('required');
                        $('#category-required').addClass('d-none');
                    }
                    $('#audio-service').get(0).reset();
                    $(this).val(status);
                })
            })

            function getSubCategory(category_id) {
                var get_subcategory_list = "{{ route('ajax-list', [ 'type' => 'subcategory_list','category_id' =>'']) }}" + category_id;
                get_subcategory_list = get_subcategory_list.replace('amp;', '');

                $.ajax({
                    url: get_subcategory_list,
                    success: function(result) {
                        $('#subcategory_id').select2({
                            width: '100%',
                            placeholder: "{{ trans('messages.select_name',['select' => trans('messages.subcategory')]) }}",
                            data: result.results
                        });
                        $('#subcategory_id').val('').trigger('change');
                    }
                });
            }
        })(jQuery);
    </script>
    @endsection
</x-master-layout>