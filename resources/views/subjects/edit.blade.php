<!-- /resources/views/subjects/edit.blade.php -->
@extends('layouts.master')

@section('content')

	@include('tinymce.subject')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/subjects'); !!}">Subjects</a></li>
	  <li class="active">{{ $subject->subject_name }}</li>
	</ul>

	<h2>Edit Subject "{{ $subject->subject_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($subject, ['method' => 'PATCH', 'route' => ['subjects.update', $subject->id]]) !!}
	@include('subjects/partials/_form', ['submit_text' => 'Edit Subject'])
	{!! Form::close() !!}
@endsection
