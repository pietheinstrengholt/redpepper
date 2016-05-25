@extends('layouts.master')
<head>
	<link rel="stylesheet" href="{{ URL::asset('css/landing-page.css') }}">
</head>
<body class="startpage">

<!-- Header -->
<div id="intro-header" class="intro-header" style="background: url({{ URL::asset('/img/background') }}/{!! App\Helper::setting('homescreen_image') !!}) no-repeat center center; background-size:cover; ">
	<div class="container">

		<div id="intro-header-row" class="row">
			<div id="intro-header-col-lg-12" class="col-lg-12">
				<div class="intro-message">

					@if (count($errors) > 0)
						<div class="alert alert-danger alert-dismissible">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

		{{--*/ $i=0; /*--}}
		<div class="row row-eq-height" style="margin-top: 20px;">
		@foreach ($subjects as $subject)
		<div class="col-md-3 col-sm-6 hero-feature">
			@if ($subject->visible == "False")
				<div class="thumbnail" style="background-color: #FEF0C9 !important;">
			@else
				<div class="thumbnail">
			@endif
				<div class="caption" style="padding:0px;">
					<div class="clearfix"></div>
					<div class="content-container" style="margin-top:-5px;">
						<h3 class="center"><a href="{{ url('sections') }}?subject_id={{ $subject->id }}">{{ $subject->subject_name }}</a></h3>
						<p class="center">{{ $subject->subject_description }}</p>
						<p class="p-more-info">
							<a href="{{ url('sections') }}?subject_id={{ $subject->id }}" class="btn btn-primary">More info</a>
						</p>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
		{{--*/ $i++; /*--}}
		@if ($i%4 == 0)
			</div><div class="row">
		@endif

		@endforeach
		</div>

		<div id="page-content2" class="row" style="margin: 0px;">
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
