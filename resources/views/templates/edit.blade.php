<!-- /resources/views/templates/edit.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>Edit Template "{{ $template->template_name }}"</h2>

	{!! Form::model($template, ['method' => 'PATCH', 'route' => ['sections.templates.update', $section->id, $template->id]]) !!}
	@include('templates/partials/_form', ['submit_text' => 'Edit Template'])
	{!! Form::close() !!}
@endsection