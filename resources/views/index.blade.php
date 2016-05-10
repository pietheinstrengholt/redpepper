@extends('layouts.master')
<head>
	<link rel="stylesheet" href="{{ URL::asset('css/landing-page.css') }}">
</head>
<body class="startpage">

<!-- Header -->
<div id="intro-header" class="intro-header">
	<div class="container">

		<div id="intro-header-row" class="row">
			<div id="intro-header-col-lg-12" class="col-lg-12">
				<div class="intro-message">

					@if (count($errors) > 0)
						<div class="alert alert-danger">
						<ul>
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
						</ul>
						</div>
					@endif

					<h1>{!! App\Helper::setting('main_message1') !!}</h1>
					<h3>{!! App\Helper::setting('main_message2') !!}</h3>
					<hr style="margin-right:1000px;" class="intro-divider">
					<ul class="list-inline intro-social-buttons">
						<li><a href="{{ url('sections') }}" class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>All reports</span></a></li>
						<li><a href="doc/FRC_RADAR_Tooling_User_Manual.pdf" class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>Instruction manual</span></a></li>
						<li><a href="{{ url('advancedsearch') }}" class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-search" aria-hidden="true"></span>Advanced</span></a></li>
						<li id="changes"><p class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>Latest changes</span></p></li>
					</ul>
				</div>
			</div>
		</div>

		<div id="page-content" class="row">

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="{{ url('sections') }}?subject_id=1">COREP</a></h3>
						<p class="center">Common Reporting (COREP) is the standardized reporting framework issued by the EBA for the Capital Requirements Directive reporting.</p>
						<p class="p-more-info">
							<a href="{{ url('sections') }}?subject_id=1" class="btn btn-danger">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="{{ url('sections') }}?subject_id=2">FINREP</a></h3>
						<p class="center">FINREP reporting is a standardized EU-wide framework for reporting financial (accounting) data.<br><br></p>
						<p class="p-more-info">
							<a href="{{ url('sections') }}?subject_id=2" class="btn btn-primary">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="{{ url('sections') }}?subject_id=3">Liquidity reports</a></h3>
						<p class="center">Liquidity covers the Liquidity coverage ratio templates, the stable funding templates and other liquidity reports.<br><br></p>
						<p class="p-more-info">
							<a href="{{ url('sections') }}?subject_id=3" class="btn btn-success">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="{{ url('sections') }}?subject_id=4">Other reports</a></h3>
						<p class="center">This section covers all other regulatory reports, e.g. issues by the local NSA (National Supervisory Authority).<br><br></p>
						<p class="p-more-info">
							<a href="{{ url('sections') }}?subject_id=4" class="btn btn-warning">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-12 col-sm-12" id="footer">
				<p class="muted credit" style="margin: 20px 0px 0px 0px; color: #999999;">Owned by the <a style="color:#0088cc;" href="mailto:FRC@nl.abnamro.com">FRC team</a>, developed with close collaboration by <a style="color:#0088cc;" href="mailto:piethein.strengholt@nl.abnamro.com">Piethein Strengholt</a><img style="margin-bottom: 5px; margin-left: 3px;" src="{{ URL::asset('img/pepper-small.png') }}" alt="redpepper" height="16" width="16"></p>
				@if (file_exists(base_path() . '/version'))
					<p class="muted credit"style="color: #999999;"><small>version: 2.0-{{ file_get_contents(base_path() . '/version') }}</small></p>
				@endif
			</div>

		</div>

	</div>
	<!-- /.container -->

</div>

@include('modal')

</body>
