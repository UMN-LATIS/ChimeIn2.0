<!DOCTYPE html>
<html lang="en">
<head>
	<title>ChimeIn</title>
	<!--	None of this mobile stuff will work if you don't use a viewport meta tag -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta charset="UTF-8">

    	<meta name="csrf-token" content="{{ csrf_token() }}">
        @vite(['resources/js/app.js'])
			@yield('header')
		</head>
		<body>

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
