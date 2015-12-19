<!-- /resources/views/sections/edit.blade.php -->
@extends('layouts.master')

@section('content')
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