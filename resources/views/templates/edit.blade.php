<!-- /resources/views/templates/edit.blade.php -->
@extends('layouts.master')

@section('content')
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

	{!! Form::model($template, ['method' => 'PATCH', 'route' => ['sections.templates.update', $section->id, $template->id]]) !!}
	@include('templates/partials/_form', ['submit_text' => 'Edit Template'])
	{!! Form::close() !!}
@endsection