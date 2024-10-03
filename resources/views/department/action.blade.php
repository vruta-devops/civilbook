
<?php
$auth_user = authSession();
?>
{{ Form::open(['route' => ['department.destroy', $department->id], 'method' => 'delete','data--submit'=>'department'.$department->id]) }}
<div class="d-flex justify-content-end align-items-center">
    @if(!$department->trashed())
        @if($auth_user->can('department edit'))
            <a class="mr-2" href="{{ route('department.create',['id' => $department->id]) }}"
               title="{{ __('messages.update_form_title',['form' => __('messages.department') ]) }}"><i
                        class="fas fa-pen text-primary"></i></a>
        @endif
    @endif
    @if(auth()->user()->hasAnyRole(['admin']) && $department->trashed())
        <a href="{{ route('department.action',['id' => $department->id, 'type' => 'restore']) }}"
           title="{{ __('messages.restore_form_title',['form' => __('messages.department') ]) }}"
           data--submit="confirm_form"
           data--confirmation='true'
           data--ajax='true'
           data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.department') ]) }}"
           data-message='{{ __("messages.restore_msg") }}'
           data-datatable="reload"
           class="mr-2">
            <i class="fas fa-redo text-secondary"></i>
        </a>
        <a href="{{ route('department.action',['id' => $department->id, 'type' => 'forcedelete']) }}"
           title="{{ __('messages.forcedelete_form_title',['form' => __('messages.department') ]) }}"
           data--submit="confirm_form"
           data--confirmation='true'
           data--ajax='true'
           data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.department') ]) }}"
           data-message='{{ __("messages.forcedelete_msg") }}'
           data-datatable="reload"
           class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
</div>
{{ Form::close() }}
