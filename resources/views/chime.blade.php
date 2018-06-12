<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chime</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link
        rel="stylesheet"
        type="text/css"
        media="screen"
        href="../css/app.css" />
</head>
<body>
    <div id="app">
        <Chime :user="{{$user}}"></Chime>
    </div>
    <script>window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'</script>
    <script src="{{ mix('js/app.js') }}"></script>

</body>
</html>