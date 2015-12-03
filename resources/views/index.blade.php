@extends('layouts.master')
<head>
	<link rel="stylesheet" href="{{ URL::asset('css/landing-page.css') }}">
</head>
<body class="startpage" style="height: 100%;">

<!-- Header -->
<div id="intro-header" class="intro-header">
	<div class="container">

		<div id="intro-header-row" class="row">
			<div id="intro-header-col-lg-12" class="col-lg-12">
				<div class="intro-message">
					<h1>FRC RADAR Tool</h1>
					<h3>Zichtbaar in control</h3>
					<hr style="margin-right:1000px;" class="intro-divider">
					<ul class="list-inline intro-social-buttons">
						<li><a href="<?php echo url('sections'); ?>" class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-list-alt" aria-hidden="true"></span>All regulatory reports</span></a></li>
						<li><a href="doc/FRC_RADAR_Tooling_User_Manual.pdf" class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-folder-open" aria-hidden="true"></span>Instruction manual</span></a></li>
						<li><a href="<?php echo url('advancedsearch'); ?>" class="btn btn-default btn-lg"><span class="network-name"><span style="margin-right:3px;" class="glyphicon glyphicon-search" aria-hidden="true"></span>Advanced search</span></a></li>

					</ul>
				</div>
			</div>
		</div>

		<div id="page-content">

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="<?php echo url('sections'); ?>?group=corep">COREP</a></h3>
						<p class="center">Common Reporting (COREP) is the standardized reporting framework issued by the EBA for the Capital Requirements Directive reporting.</p>
						<p class="p-more-info">
							<a href="<?php echo url('sections'); ?>?group=corep" class="btn btn-danger">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="<?php echo url('sections'); ?>?group=finrep">FINREP</a></h3>
						<p class="center">FINREP reporting is a standardized EU-wide framework for reporting financial (accounting) data.<br><br></p>
						<p class="p-more-info">
							<a href="<?php echo url('sections'); ?>?group=finrep" class="btn btn-primary">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="<?php echo url('sections'); ?>?group=liquidity">Liquidity reports</a></h3>
						<p class="center">Liquidity covers the Liquidity coverage ratio templates, the stable funding templates and other liquidity reports.<br><br></p>
						<p class="p-more-info">
							<a href="<?php echo url('sections'); ?>?group=liquidity" class="btn btn-success">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="col-md-3 col-sm-6 hero-feature">
				<div class="thumbnail">
					<div class="caption">
						<h3 class="center"><a href="<?php echo url('sections'); ?>?group=other">Other reports</a></h3>
						<p class="center">This section covers all other regulatory reports, e.g. issues by the local NSA (National Supervisory Authority).<br><br></p>
						<p class="p-more-info">
							<a href="<?php echo url('sections'); ?>?group=other" class="btn btn-warning">More info</a>
						</p>
					</div>
				</div>
			</div>

			<div class="container">
				<div class="row">
					<div id="footer" class="col-lg-12">
						<p class="muted credit" style="margin: 20px 0; color: #999999;">Owned by the <a style="color:#0088cc;" href="mailto:FRC@nl.abnamro.com">FRC team</a>, developed with close collaboration by <a style="color:#0088cc;" href="mailto:piethein.strengholt@nl.abnamro.com">Piethein Strengholt</a>.</p>
					</div>
				</div>
			</div>

		</div>

	</div>
	<!-- /.container -->

</div>
<!-- /.intro-header -->

<!-- Page Content -->


<!-- /.banner -->

<!-- Footer -->
<footer>

</footer>
</body>
