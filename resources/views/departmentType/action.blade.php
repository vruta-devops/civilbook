
<?php
$auth_user = authSession();
?>
{{ Form::open(['route' => ['department-types.destroy', $departmentType->id], 'method' => 'delete','data--submit'=>'department-types'.$departmentType->id]) }}
<div class="d-flex justify-content-end align-items-center">
    @if($auth_user->hasRole('admin'))
        <a class="mr-2" href="{{ route('department-types.create',['id' => $departmentType->id]) }}"
           title="{{ __('messages.update_form_title',['form' => __('messages.department-types') ]) }}"><i
                    class="fas fa-pen text-primary"></i></a>
    @endif
</div>
{{ Form::close() }}
