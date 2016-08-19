<!-- /resources/views/types/create.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/types'); !!}">Types</a></li>
	  <li class="active">Create new type</li>
	</ul>

	<h2>Create Type</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\TechnicalType, ['route' => ['types.store']]) !!}
	@include('types/partials/_form', ['submit_text' => 'Create Type'])
	{!! Form::close() !!}
@endsection
