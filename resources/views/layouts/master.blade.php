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
	<meta charset="utf-8">

	<!-- Meta base url, needed for javascript location -->
	<meta name="base_url" content="{{ URL::to('/') }}">
	<!-- IE Console log fix -->
	<script type="text/javascript"> if (!window.console) console = {log: function() {}}; </script>

	@show
</head>

<!--[if lt IE 9]>
	<div id="ie8"><p>IE8 is no longer supported. You're using a unsupported version of Internet Explorer. Please upgrade or use Google Chrome instead.</p></div>
<![endif]-->

<nav class="navbar navbar-default navbar-fixed-top">
	<div class="container-fluid">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
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
				  @if (Auth::user()->role == "superadmin")
					  <li><a href="{{ URL::to('/settings') }}"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> Settings</a></li>
					  <li class="divider"></li>
					  <li><a href="{{ URL::to('/terms') }}"><span class="glyphicon glyphicon-grain" aria-hidden="true"></span> Terms</a></li>
					  <li class="divider"></li>
					  <li><a href="{{ URL::to('/types') }}"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit types</a></li>
					  <li><a href="{{ URL::to('/sources') }}"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit sources</a></li>
					  <li><a href="{{ URL::to('/departments') }}"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit departments</a></li>
					  <li><a href="{{ URL::to('/users') }}"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span> Edit users</a></li>
					  <li class="divider"></li>
					  <li><a href="{{ URL::to('/csv/import') }}"><span class="glyphicon glyphicon-import" aria-hidden="true"></span> Import technical</a></li>
					  <li class="divider"></li>
				  @endif
				  <li><a href="{{ URL::to('/changerequests') }}"><span class="glyphicon glyphicon-tasks" aria-hidden="true"></span> Change requests</a></li>
				  @if (Auth::user()->role == "superadmin" || Auth::user()->role == "admin")
					  <li class="divider"></li>
					  <li><a href="{{ URL::to('/logs') }}"><span class="glyphicon glyphicon-certificate" aria-hidden="true"></span> User activities</a></li>
					  <li class="divider"></li>
				  @endif
				  @if (Auth::user()->role == "superadmin" || Auth::user()->role == "admin" || Auth::user()->role == "builder")
					<li><a href="{{ URL::to('/excel/upload') }}"><span class="glyphicon glyphicon-paperclip" aria-hidden="true"></span> Upload excel template</a></li>
					<li class="divider"></li>
					<li><a href="{{ URL::to('/sections/0/templates/create') }}"><span class="glyphicon glyphicon-list-alt" aria-hidden="true"></span> Create new template</a></li>
				  @endif
				</ul>
			  </li>
			</ul>
			@endif
			<ul class="nav navbar-nav navbar-right">
			@if (Auth::guest())
			  <li><a href="{{ URL::to('/auth/login') }}">Login</a></li>
			  <li><a href="{{ URL::to('/auth/register') }}">Register</a></li>
			  <li><a href="{{ URL::to('/password/email') }}">Reset</a></li>
			@else
			  <li><a href="{{ URL::to('/auth/logout') }}">Logout</a></li>
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
			<div id="session-alert" class="alert alert-info alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<p>{!! Session::get('message') !!}</p>
			</div>
		@endif

		<!-- Content -->
		@yield('content')

	</div>

	</body>
</html>
