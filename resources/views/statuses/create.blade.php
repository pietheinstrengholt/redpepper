<!-- /resources/views/statuses/create.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/statuses'); !!}">Statuses</a></li>
	  <li class="active">Create new status</li>
	</ul>

	<h2>Create Status</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\Status, ['route' => ['statuses.store']]) !!}
	@include('statuses/partials/_form', ['submit_text' => 'Create Status'])
	{!! Form::close() !!}
@endsection