<!DOCTYPE html>
<html>
<head>
	@section('head')
	<title>
	@section('title')
	ABN AMRO FRC RADAR Tool
	@show
	</title>

	<!-- External scripts are placed here -->
	<script src="{{ URL::asset('js/jquery-1.11.3.min.js') }}"></script>
	<script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>
	<script src="{{ URL::asset('js/app.js') }}"></script>

	<!-- CSS -->
	<link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<!-- Meta base url, needed for javascript location -->
	<meta name="base_url" content="{{ URL::to('/') }}">
	
	<!-- IE Console log fix -->
    <script type="text/javascript"> if (!window.console) console = {log: function() {}}; </script>	
	
	<style type="text/css">
		body {
			padding-bottom: 40px;
		}
		.sidebar-nav {
			padding: 9px 0;
		}
		@media (max-width: 980px) {
			/* Enable use of floated navbar text */
			padding-top: 0px;
			.navbar-text.pull-right {
				float: none;
				padding-left: 5px;
				padding-right: 5px;
			}
		}
	</style>

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
	<!--[if lt IE 9]>
	<script src="{{ URL::asset('js/html5shiv.js') }}"></script>
	<script src="{{ URL::asset('js/respond.min.js') }}"></script>
	<link href="{{ URL::asset('css/ie8.css') }}" rel="stylesheet" media="screen">
	<![endif]-->

	@show
</head>

<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
			  <span class="sr-only">Toggle navigation</span>
			  <span class="icon-bar"></span>
			  <span class="icon-bar"></span>
			  <span class="icon-bar"></span>
			</button>
			<a class="navbar-brand" href="{{ URL::to('/') }}">Home</a>
		</div>

		<div class="collapse navbar-collapse" id="navbar-collapse">
			<ul class="nav navbar-nav">
			  <li><a href="{{ URL::to('/manuals') }}">Manuals</a></li>
			  <li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Sections <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
				  @if ( $sections->count() )
					@foreach( $sections as $section )
						<li><a href="{{ route('sections.show', $section->id) }}">{{ $section->section_name }}</a></li>
					@endforeach
				  @endif
				</ul>
			  </li>
			</ul>
			<form class="navbar-form navbar-left" role="search" action="{{ URL::to('/search') }}" method="post">
			<input type="hidden" name="_token" value="{!! csrf_token() !!}">
			  <div class="form-group">
				<input type="text" name="search" class="form-control" placeholder="Search for content">
			  </div>
			  <button type="submit" class="btn btn-default">Submit</button>
			</form>
			@if (!Auth::guest())
			<ul class="nav navbar-nav">
			  <li class="dropdown">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin menu <span class="caret"></span></a>
				<ul class="dropdown-menu" role="menu">
				  <li><a href="{{ URL::to('/types') }}">Edit types</a></li>
				  <li><a href="{{ URL::to('/sources') }}">Edit sources</a></li>
				  <li><a href="{{ URL::to('/departments') }}">Edit departments</a></li>
				  <li><a href="{{ URL::to('/users') }}">Edit users</a></li>
				  <li class="divider"></li>
				  <li><a href="{{ URL::to('/csv/importcontent') }}">Import content</a></li>
				  <li><a href="{{ URL::to('/csv/importfields') }}">Import fields</a></li>
				  <li><a href="{{ URL::to('/csv/importrows') }}">Import rows</a></li>
				  <li><a href="{{ URL::to('/csv/importcolumns') }}">Import columns</a></li>
				  <li><a href="{{ URL::to('/csv/importtech') }}">Import technical</a></li>
				  <li class="divider"></li>
				  <li><a href="{{ URL::to('/changerequests') }}">Change requests</a></li>
				  <li class="divider"></li>
				  <li><a href="{{ URL::to('/logs') }}">User activities</a></li>
				  <li class="divider"></li>
				  <li><a href="{{ URL::to('/excel/upload') }}">Upload excel template</a></li>
				</ul>
			  </li>
			</ul>
			@endif
			<ul class="nav navbar-nav navbar-right">
			@if (Auth::guest())
			  <li><a href="{{ URL::to('//auth/login') }}">Login</a></li>
			  <li><a href="{{ URL::to('//auth/register') }}">Register</a></li>
			@else
			  <li><a href="{{ URL::to('//auth/logout') }}">Logout</a></li>
			@endif
			</ul>
		</div><!-- /.navbar-collapse -->
	</div><!-- /.container-fluid -->
</nav>

	<body>
	<!-- Container -->
	<div class="container">

	<!-- Session content -->
	@if (Session::has('message'))
		<div id="session-alert" class="alert alert-warning alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<p>{{ Session::get('message') }}</p>
		</div>
	@endif


	<!-- Content -->
	@yield('content')

	</div>

	@section('footer_scripts')
	<!-- Add Internet Explorer console log function -->
	<script type="text/javascript"> if (!window.console) console = {log: function() {}}; </script>
	@show

    </body>
</html>
