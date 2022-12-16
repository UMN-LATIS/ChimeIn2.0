<!DOCTYPE html>
<html lang="en">
<head>
	<title>ChimeIn</title>
	<!--	None of this mobile stuff will work if you don't use a viewport meta tag -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    @vite(['resources/assets/js/app.ts'])
</head>
<body>
    @yield('header')

		<!-- END HEADER -->

		<main role="main">
			
			<div class="home" id="app">
				
				@yield('content')


			</div>

		</main>
	
		
					<!-- END UofM FOOTER -->
	
				
				<script>window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'</script>
				<script>window.lti_launch = '{{ session('lti_launch') }}'</script>

				@yield('footer')
</body>
</html>
