<!-- /resources/views/terms/show.blade.php -->
@extends('layouts.master')

@section('content')

	<head>
	<ul class="breadcrumb breadcrumb-section">
	 <li><a href="{!! url('/'); !!}">Home</a></li>
	 <li><a href="{!! url('/terms'); !!}">Terms</a></li>
	 <li class="active">{{ $term->term_name }}</li>
	</ul>

	<div id="term" class="row">
		<div class="col-xs-6">
			<dl class="dl-horizontal">
			<dt>Term name:</dt>
			<dd id="term_name">{{ $term->term_name }}</dd>
			</dl>
		</div>

		<div class="col-xs-12">
			<dl class="dl-horizontal">
			<dt>Term definition:</dt>
			<dd id="term_description">{!! App\Helper::contentAdjust($term->term_description) !!}</dd>
			</dl>
		</div>
	</div>

@endsection
