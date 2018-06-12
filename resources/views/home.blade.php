<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Home</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/app.css" />
</head>
<body>
    <div id="app">
        <Home :user="{{$user}}"></Home>
    </div>
    <script>window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'</script>
    <script src="{{ mix('js/app.js') }}"></script>
</body>
</html>