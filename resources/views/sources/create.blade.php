<!-- /resources/views/sources/create.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/sources'); !!}">Sources</a></li>
	  <li class="active">Create new source</li>
	</ul>

	<h2>Create Source</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\TechnicalSource, ['route' => ['sources.store']]) !!}
	@include('sources/partials/_form', ['submit_text' => 'Create Source'])
	{!! Form::close() !!}
@endsection
