<!-- /resources/views/subjects/create.blade.php -->
@extends('layouts.master')

@section('content')

	@include('tinymce.subject')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/subjects'); !!}">Subjects</a></li>
	  <li class="active">Create new subject</li>
	</ul>

	<h2>Create subject</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\Subject, ['route' => ['subjects.store']]) !!}
	@include('subjects/partials/_form', ['submit_text' => 'Create Subject'])
	{!! Form::close() !!}
@endsection
