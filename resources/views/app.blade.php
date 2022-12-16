<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <title>ChimeIn</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/assets/js/app.ts'])
</head>

<body>
  @if (session('lti_notice'))
    <div class="alert alert-success">
      {!! session('lti_notice') !!}
    </div>
  @endif

  @if (session('lti_error'))
    <div class="alert alert-danger m-3">
      <strong>Notice: </strong> {!! session('lti_error') !!}
    </div>
  @endif
  <div id="modals"></div>
  <div id="app">
    <router-view :user="{{ $user }}"></router-view>
  </div>
  <script>
    window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'
    window.lti_launch = '{{ session('lti_launch') }}'
  </script>
</body>

</html>
