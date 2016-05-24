<!-- /resources/views/subjects/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
	<script>tinymce.init({ 	
		selector:'textarea#subject_longdesc',
		height: 400,
		plugins: [
			'link image'
		],
		menubar: '',
		toolbar: 'undo redo | alignleft aligncenter alignright | bold italic | link | bullist numlist',
		body_class: 'form-control',
		statusbar: false,
		content_style: "p {margin-top: -4px;} ol,ul,p {color: #2c3e50; font-size: 15px;}"
	});</script>

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