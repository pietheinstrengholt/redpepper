<!-- /resources/views/templates/edit.blade.php -->
@extends('layouts.master')

@section('content')

	@include('tinymce.template')

	<ul class="breadcrumb breadcrumb-section">
		<li><a href="{!! url('/'); !!}">Home</a></li>
		@if ($section->subject->parent)
			<li><a href="{{ route('subjects.show', $template->section->subject->parent) }}">{{ $template->section->subject->parent->subject_name }}</a></li>
		@endif
		<li><a href="{{ route('subjects.show', $template->section->subject) }}">{{ $template->section->subject->subject_name }}</a></li>
		<li><a href="{{ route('subjects.sections.show', array($template->section->subject, $template->section)) }}">{{ $template->section->section_name }}</a></li>
		<li class="active">{{ $template->template_name }}</li>
	</ul>

	<h2>Edit Template "{{ $template->template_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($template, ['method' => 'PATCH', 'route' => ['subjects.sections.templates.update', $subject->id, $section->id, $template->id]]) !!}
	@include('templates/partials/_form', ['submit_text' => 'Edit Template'])
	{!! Form::close() !!}
@endsection
