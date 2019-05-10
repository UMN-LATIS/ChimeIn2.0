<!DOCTYPE html>
<html lang="en">
<head>
	<title>ChimeIn</title>
	<!--	None of this mobile stuff will work if you don't use a viewport meta tag -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta charset="UTF-8">

    	<meta name="csrf-token" content="{{ csrf_token() }}">
    	<link rel="stylesheet" href="/umn/css/2015-tc.css">
    	<script src="/umn/js/umnhf-2015.js" type="text/javascript"></script>
		<script src="/umn/js/html5shiv-printshiv.js" type="text/javascript"></script>
		<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Amatic+SC" rel="stylesheet">
		<link href="https://fonts.googleapis.com/css?family=Itim" rel="stylesheet">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
	    <link href="{{ mix('css/app.css') }}" rel="stylesheet" type="text/css">
			@yield('header')
		</head>
		<body>
			<!-- BEGIN HEADER -->
			<header class="umnhf" id="umnhf-h" role="banner">
				<!-- Skip Links: Give your nav and content elements the appropriate ID attributes -->
				<div id="skipLinks"><a href="#main-nav">Main navigation</a><a href="#main-content">Main content</a></div>
				<div class="printer"><div class="left"></div><div class="right"><strong>University of Minnesota</strong><br />http://twin-cities.umn.edu/<br />612-625-5000</div></div>
				<div class="umnhf" id="umnhf-h-mast">
					<a class="umnhf" id="umnhf-h-logo" href="http://twin-cities.umn.edu/"><span>Go to the U of M home page</span></a>
					<ul class="umnhf" id="umnhf-h-ql">
						<li><a href="http://onestop.umn.edu/">One Stop</a></li>
						<li class="umnhf"><a href="https://www.myu.umn.edu/">MyU <span></span>: For Students, Faculty, and Staff</a></li>
					</ul>
				<!-- Button below is for dropdown toggle, only visible on mobile screens. If using
				a non-dropdown version you can delete this tag -->
				<button class="umnhf" id="umnhf-m-search">Search</button>
			</div>
			<form class="umnhf" id="umnhf-h-search" action="//search.umn.edu/tc/" method="get" title="Search Websites and People" role="search">
				<label class="umnhf" for="umnhf-h-st">Search</label>
				<input class="umnhf" id="umnhf-h-st" type="text" name="q" />
				<label class="umnhf" for="umnhf-h-sb">Submit search query</label>
				<input class="umnhf" id="umnhf-h-sb" type="submit" value="">
			</form>
		</header>
		<!-- END HEADER -->


		<a name="main-nav"></a>
		<a name="main-content"></a>
		<main id="umn-main" role="main">
			
			<div class="home" id="app">
				
				@yield('content')


			</div>

		</main>
		<footer id="page-footer">
			<div class="regions-2-only">
				<div class="region-container">
					<div class="region-1 ">
						<div class="panel-pane pane-cla-glue-unit-social-pane">

							<h1 class="pane-title">Connect</h1>


							<div class="connect-info">
								<span><i class="fa fa-envelope"></i>
									<div class="field-email">
										<a href="mailto:cla@umn.edu">cla@umn.edu</a>  </div>
									</span>
									<span><i class="fa fa-phone"></i><a href="tel:6126252020">612-625-2020</a></span>
								</div>
								<p class="social">
									<a href="http://facebook.com/umncla"><i class="fa fa-facebook-square fa-lg"><span>Visit College of Liberal Arts on Facebook</span></i></a>
									<a href="http://twitter.com/umncla"><i class="fa fa-twitter fa-lg"><span>Visit College of Liberal Arts on Twitter</span></i></a>
									<a href="https://www.linkedin.com/groups?gid=2997319&amp;trk=anet_ug_hm&amp;home="><i class="fa fa-linkedin fa-lg"><span>Visit College of Liberal Arts on LinkedIn</span></i></a>
									<a href="http://youtube.com/umncla"><i class="fa fa-youtube-play fa-lg"><span>Visit College of Liberal Arts on YouTube</span></i></a>
								</p>

							</div>
						</div>
						<div class="region-2 ">
							<div class="panel-pane pane-cla-glue-unit-give-pane">

								<h1 class="pane-title">Make a Gift</h1>


								<div>
									<a href="/give">Find information on ways to give to the College of Liberal Arts</a>
								</div>


							</div>
						</div>
					</div>
					<div class="region-container">
						<div class="region-3 ">
						</div>
						<div class="region-4 ">
							<div class="panel-pane pane-cla-glue-unit-contact-pane">



								<img alt="College of Liberal Arts" class="logo" src="https://cla.umn.edu/sites/cla.umn.edu/themes/cla_base/prd/img/CLAwordmark_DarkGray.svg">

								<div class="field-campus-address">

									215

									<a href="
									https://campusmaps.umn.edu/johnston-hall">Johnston Hall</a>

									<div class="field-street-address">
										<div class="street-block"><div class="thoroughfare">101 Pleasant St SE</div></div><div class="addressfield-container-inline locality-block country-US"><span class="locality">Minneapolis</span>, <span class="state">MN</span> <span class="postal-code">55455</span></div>  </div>


									</div>

									<div class="field-intranet-link">
										<a href="https://cla.umn.edu/neighborhood">Intranet</a>  </div>


									</div>
								</div>
							</div>
						</div>
					</footer>

					<!-- BEGIN UofM FOOTER -->
					<footer id="umnhf-f" class="umnhf" role="contentinfo">
						<nav id="umnhf-f-myu">
							<h3 class="umnhf-f-title visually-hidden">For Students, Faculty, and Staff</h3>
							<ul>
								<li><a href="http://onestop.umn.edu/">One Stop</a></li>
								<li><a href="https://www.myu.umn.edu/">My U <span></span></a></li>
							</ul>
						</nav>
						<div class="container">
							<small>&copy; <span id="cdate">2015</span> Regents of the University of Minnesota. All rights reserved. The University of Minnesota is an equal opportunity educator and employer. <a href="http://privacy.umn.edu">Privacy Statement</a></small>
						</div>
						<!-- Optional last updated link-->
					</footer>
					<!-- END UofM FOOTER -->
				</body>
				
				<script>window.pusherKey = '{{ env('PUSHER_APP_KEY') }}'</script>
				<script src="{{ mix('js/app.js') }}"></script>

				@yield('footer')

				</html>
