<!-- /resources/views/types/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/types'); !!}">Types</a></li>
	  <li class="active">{{ $type->type_name }}</li>
	</ul>

	<h2>Edit Type "{{ $type->type_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($type, ['method' => 'PATCH', 'route' => ['types.update', $type->id]]) !!}
	@include('types/partials/_form', ['submit_text' => 'Edit Type'])
	{!! Form::close() !!}
@endsection
