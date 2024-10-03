
<?php
    $auth_user= authSession();
?>
{{ Form::open(['route' => ['service.destroy', $audio->id], 'method' => 'delete','data--submit'=>'service'.$audio->id]) }}
<div class="d-flex justify-content-end align-items-center">
@if(!$audio->trashed())
        @if(checkCustomPermission('audio service edit', $auth_user))
        <a href="{{ route('service.audio-service-post',['id'=> $audio->id ]) }}" class="mr-2"  title="{{ __('messages.update_form_title',['form' => __('messages.audio_service') ]) }}"><i class="fas fa-pen text-primary"></i></a>
        @endif
        @if(checkCustomPermission('audio service delete'))
        <a class="mr-2" href="{{ route('service.audio-service-destroy', $audio->id) }}" data--submit="audioservice{{$audio->id}}"
            data--confirmation='true'
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.audio_service') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.audio_service') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt text-danger"></i>
        </a>
        @endif
    @endif
    @if(auth()->user()->hasAnyRole(['admin']) && $audio->trashed())
        <a href="{{ route('service.audio-service-action',['id' => $audio->id, 'type' => 'restore']) }}"
            title="{{ __('messages.restore_form_title',['form' => __('messages.audio_service') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.restore_form_title',['form'=>  __('messages.audio_service') ]) }}"
            data-message='{{ __("messages.restore_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="fas fa-redo text-primary"></i>
        </a>
        <a href="{{ route('service.audio-service-action',['id' => $audio->id, 'type' => 'forcedelete']) }}"
            title="{{ __('messages.forcedelete_form_title',['form' => __('messages.audio_service') ]) }}"
            data--submit="confirm_form"
            data--confirmation='true'
            data--ajax='true'
            data-title="{{ __('messages.forcedelete_form_title',['form'=>  __('messages.audio_service') ]) }}"
            data-message='{{ __("messages.forcedelete_msg") }}'
            data-datatable="reload"
            class="mr-2">
            <i class="far fa-trash-alt text-danger"></i>
        </a>
    @endif
</div>
{{ Form::close() }}
