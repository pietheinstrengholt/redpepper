<!-- /resources/views/sources/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/sources'); !!}">Sources</a></li>
	  <li class="active">{{ $source->source_name }}</li>
	</ul>

	<h2>Edit Source "{{ $source->source_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($source, ['method' => 'PATCH', 'route' => ['sources.update', $source->id]]) !!}
	@include('sources/partials/_form', ['submit_text' => 'Edit Source'])
	{!! Form::close() !!}
@endsection