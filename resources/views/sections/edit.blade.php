<!-- /resources/views/sections/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

	<script>tinymce.init({ 	
		selector:'textarea#section_description',
		valid_elements: "p[style],a[href|target],strong/b,i/em,br,table,tbody,thead,tr,td,ul,ol,li",
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
		valid_elements: "p[style],a[href|target],strong/b,i/em,br,table,tbody,thead,tr,td,ul,ol,li",
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
	<li class="active">{{ $section->section_name }}</li>
	</ul>

	<h2>Edit Section "{{ $section->section_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($section, ['method' => 'PATCH', 'route' => ['sections.update', $section->id]]) !!}
	@include('sections/partials/_form', ['submit_text' => 'Edit Section'])
	{!! Form::close() !!}
@endsection