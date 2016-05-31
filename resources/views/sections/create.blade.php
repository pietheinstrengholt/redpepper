<!-- /resources/views/sections/create.blade.php -->
@extends('layouts.master')

@section('content')

	<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

	<script>tinymce.init({ 	
		selector:'textarea#section_description',
		height: 200,
		plugins: [
			'link image'
		],
		menubar: '',
		toolbar: 'undo redo | bold italic | link | bullist numlist',
		body_class: 'form-control',
		statusbar: false,
		content_style: "p {margin-top: -4px;} ol,ul,p {color: #2c3e50; font-size: 15px;}"
	});</script>

	<script>tinymce.init({ 	
		selector:'textarea#section_longdesc',
		height: 300,
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
	<li><a href="{!! url('/sections/'); !!}">Sections</a></li>
	<li class="active">Create Section</li>
	</ul>

	<h2>Create Section</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\Section, ['route' => ['sections.store']]) !!}
	@include('sections/partials/_form', ['submit_text' => 'Create Section'])
	{!! Form::close() !!}
@endsection