<!-- /resources/views/sections/create.blade.php -->
@extends('layouts.master')

@section('content')

	@include('tinymce.section')

	<ul class="breadcrumb breadcrumb-section">
		<li><a href="{!! url('/'); !!}">Home</a></li>
		<li><a href="{{ route('subjects.show', $subject->id) }}">{{ $subject->subject_name }}</a></li>
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

	{!! Form::model(new App\Section, array('route' => array('subjects.sections.store', $subject))) !!}
	@include('sections/partials/_form', ['submit_text' => 'Create Section'])
	<input type="hidden" name="created_by" value="{!! Auth::user()->id !!}">
	{!! Form::close() !!}
@endsection
