<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <title>ChimeIn</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  {{-- Must load before the bundle: the app waits on Office.onReady. --}}
  <script src="https://appsforoffice.microsoft.com/lib/1/hosted/office.js"></script>
  @vite(['resources/assets/js/office/content.ts'])
</head>

<body class="chimein-office">
  <div id="office-content"></div>
  <script>
    window.chimeInOffice = {
      reverbKey: '{{ config('broadcasting.connections.reverb.key') }}',
      reverbPort: '{{ env('VITE_REVERB_PORT', 8080) }}',
      authStartUrl: '{{ route('office.auth.start') }}',
    };
  </script>
</body>

</html>
