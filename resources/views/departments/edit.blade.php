<!-- /resources/views/departments/edit.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>Edit Department "{{ $department->department_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($department, ['method' => 'PATCH', 'route' => ['departments.update', $department->id]]) !!}
	@include('departments/partials/_form', ['submit_text' => 'Edit Department'])
	{!! Form::close() !!}
@endsection