<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="baseUrl" content="{{env('APP_URL')}}" />
        <meta name="robots" content="noindex, nofollow">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="shortcut icon" class="site_favicon_preview" href="{{ getSingleMedia(settingSession('get'),'site_favicon',null) }}" />

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link href="{{asset('css/bootstrap.min.css')}}" rel="stylesheet"/>
        <link rel="stylesheet" href="{{asset('css/intl-tel-input.17.0.8.css')}}">
        <link rel="stylesheet" href="{{ asset('css/themes/select2.min.css')}}">
        <link rel="stylesheet" href="{{ asset('css/custom.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('css/style.css')}}"/>


    </head>
    <body class=" " >

        <div class="wrapper">
            {{ $slot }}
        </div>
         @include('partials._scripts')
    </body>
</html>
