<!-- /resources/views/terms/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	 <li><a href="{!! url('/'); !!}">Home</a></li>
	 <li><a href="{!! url('/terms'); !!}">Terms</a></li>
	 <li class="active">{{ $term->term_name }}</li>
	</ul>

	<dl class="dl-horizontal">
	<dt>Term name:</dt>
	<dd>{{ $term->term_name }}</dd>
	</dl>
	
	<dl class="dl-horizontal">
	<dt>Term definition:</dt>
	<dd>{!! App\Helper::contentAdjust($term->term_definition) !!}</dd>
	</dl>
@endsection
