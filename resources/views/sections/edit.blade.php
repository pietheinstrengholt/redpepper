<!-- /resources/views/sections/edit.blade.php -->
@extends('layouts.master')

@section('content')

	@include('tinymce.section')

	<ul class="breadcrumb breadcrumb-section">
		<li><a href="{!! url('/'); !!}">Home</a></li>
		@if ($section->subject->parent)
			<li><a href="{{ route('subjects.show', $section->subject->parent->id) }}">{{ $section->subject->parent->subject_name }}</a></li>
		@endif
		<li><a href="{{ route('subjects.show', $section->subject->id) }}">{{ $section->subject->subject_name }}</a></li>
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

	{!! Form::model($section, ['method' => 'PATCH', 'route' => ['subjects.sections.update', $subject->id, $section->id]]) !!}
	@include('sections/partials/_form', ['submit_text' => 'Edit Section'])
	{!! Form::close() !!}
@endsection
