<!DOCTYPE html>
<html>
<head>
	@section('head')
	<title>
	@section('title')
	Laravel
	@show
	</title>
	
	<!-- External scripts are placed here -->
	<script src="{{ URL::asset('js/jquery-1.11.3.min.js') }}"></script>
	<script src="{{ URL::asset('js/bootstrap.min.js') }}"></script>
	<script src="{{ URL::asset('js/cellupdate-helper.js') }}"></script>
	<script src="{{ URL::asset('js/template-helper.js') }}"></script>

	<!-- CSS -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" href="{{ URL::asset('css/app.css') }}">
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
	<a class="navbar-brand" href="<?php echo url('/'); ?>">Home</a>
	</div>

	<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
	<ul class="nav navbar-nav">
	  <li class="active"><a href="#">Link <span class="sr-only">(current)</span></a></li>
	  <li><a href="#">Link</a></li>
	</ul>
	<form class="navbar-form navbar-left" role="search">
	  <div class="form-group">
		<input type="text" class="form-control" placeholder="Search for content">
	  </div>
	  <button type="submit" class="btn btn-default">Submit</button>
	</form>
	<ul class="nav navbar-nav">
	  <li class="dropdown">
		<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin menu <span class="caret"></span></a>
		<ul class="dropdown-menu" role="menu">
		  <li><a href="<?php echo url('types'); ?>">Edit types</a></li>
		  <li><a href="<?php echo url('sources'); ?>">Edit sources</a></li>
		  <li><a href="<?php echo url('departments'); ?>">Edit departments</a></li>
		  <li class="divider"></li>
		  <li><a href="<?php echo url('changerequests'); ?>">Change requests</a></li>
		  <li class="divider"></li>
		  <li><a href="<?php echo url('excel/upload'); ?>">Upload excel template</a></li>
		</ul>
	  </li>
	</ul>	
	<ul class="nav navbar-nav navbar-right">
	  <li><a href="#">Login</a></li>
	</ul>
	</div>
	</div>
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
