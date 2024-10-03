
<?php
    $auth_user= authSession();
?>
{{ Form::open(['route' => ['providerdocument.destroy', $provider_document->id], 'method' => 'delete','data--submit'=>'providerdocument'.$provider_document->id]) }}
<div class="d-flex justify-content-end align-items-center">
    {{--    @if(!$provider_document->trashed())--}}


        @if($auth_user->can('providerdocument delete'))
        <a class="mr-3" href="{{ route('providerdocument.destroy', $provider_document->id) }}"
           data--submit="providerdocument{{$provider_document->id}}"
           data--confirmation='true'
            data--ajax="true"
            data-datatable="reload"
            data-title="{{ __('messages.delete_form_title',['form'=>  __('messages.providerdocument') ]) }}"
            title="{{ __('messages.delete_form_title',['form'=>  __('messages.providerdocument') ]) }}"
            data-message='{{ __("messages.delete_msg") }}'>
            <i class="far fa-trash-alt text-danger"></i>
        </a>
        @endif
{{--    @endif--}}

{{ Form::close() }}
