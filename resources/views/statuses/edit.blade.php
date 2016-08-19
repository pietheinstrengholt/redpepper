<!-- /resources/views/statuses/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/statuses'); !!}">Statuses</a></li>
	  <li class="active">{{ $status->status_name }}</li>
	</ul>

	<h2>Edit Status "{{ $status->status_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($status, ['method' => 'PATCH', 'route' => ['statuses.update', $status->id]]) !!}
	@include('statuses/partials/_form', ['submit_text' => 'Edit Status'])
	{!! Form::close() !!}
@endsection
