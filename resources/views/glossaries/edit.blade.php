<!-- /resources/views/glossaries/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/glossaries'); !!}">Glossaries</a></li>
	  <li class="active">{{ $glossary->glossary_name }}</li>
	</ul>

	<h2>Edit Source "{{ $glossary->glossary_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($glossary, ['method' => 'PATCH', 'route' => ['glossaries.update', $glossary->id]]) !!}
	@include('glossaries/partials/_form', ['submit_text' => 'Edit Glossary'])
	{!! Form::close() !!}
@endsection
