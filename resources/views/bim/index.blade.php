@extends('layouts.master')
<head>
	<link rel="stylesheet" href="{{ URL::asset('css/landing-page.css') }}">
</head>
<body class="startpage">

{{--*/ $buttons = array("btn-danger", "btn-primary", "btn-success", "btn-warning"); /*--}}

<!-- Header -->
<div id="bim-header" class="bim-header" style="background: url({{ URL::asset('/img/background') }}/bim-background.jpg) no-repeat center center; background-size:cover; ">
	<div class="container">

		<div id="bim-header-row" class="row">
			<div id="bim-header-col-lg-12" class="col-lg-12">
				<div class="bim-message">
					<h1>Information Model</h1>
					<h3>ABN AMRO Lexicon</h3>
				</div>
			</div>
		</div>

		<h3>Please make a selection of the following options</h3>
		<div style="height: 200px;" id="page-content1" class="row-flex row-flex-wrap row row-eq-height">
			<div class="col-md-3 col-sm-6 hero-feature">
					<div class="well" style="background-color: #128f76; opacity: 0.8;">
					<div class="caption" style="padding:0px;">
						<div class="clearfix"></div>
						<div class="content-container">
							<h3 class="center"><a style="color:white" href="{{ url('terms') }}">All terms</a></h3>
							<p class="p-more-info">
								<a href="{{ url('terms') }}"><img src="{{ URL::asset('/img') }}/science-book.png" height="80" width="80"></a>
							</p>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
					<div class="well" style="background-color: #2c3e50; opacity: 0.8;">
					<div class="caption" style="padding:0px;">
						<div class="clearfix"></div>
						<div class="content-container">
							<h3 class="center"><a style="color:white" href="{{ url('glossaries') }}">Glossaries</a></h3>
							<p class="p-more-info">
								<a href="{{ url('glossaries') }}"><img src="{{ URL::asset('/img') }}/mountain.png" height="80" width="80"></a>
							</p>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
					<div class="well" style="background-color: #f39c12; opacity: 0.8;">
					<div class="caption" style="padding:0px;">
						<div class="clearfix"></div>
						<div class="content-container">
							<h3 class="center"><a style="color:white" href="{{ url('termproperties') }}">Meta data content</a></h3>
							<p class="p-more-info">
								<a href="{{ url('termproperties') }}"><img src="{{ URL::asset('/img') }}/herbal-spa-treatment-leaves.png" height="80" width="80"></a>
							</p>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
	<!-- /.container -->

</div>

@include('modal')

</body>
