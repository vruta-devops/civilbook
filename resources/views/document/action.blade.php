
<?php
    $auth_user= authSession();
?>
{{ Form::open(['route' => ['document.destroy', $document->id], 'method' => 'delete','data--submit'=>'document'.$document->id]) }}
<div class="d-flex justify-content-end align-items-center">
        @if($auth_user->can('document delete'))
        <a class="mr-3" href="{{ route('document.destroy', $document->id) }}" data--submit="document{{$document->id}}"
           data--confirmation='true'
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.document') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.document') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt text-danger"></i>
        </a>
        @endif
{{ Form::close() }}
