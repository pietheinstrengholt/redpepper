<!-- /resources/views/glossaries/create.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/glossaries'); !!}">Glossaries</a></li>
	  <li class="active">Create new glossary</li>
	</ul>

	<h2>Create Glossary</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\Glossary, ['route' => ['glossaries.store']]) !!}
	@include('glossaries/partials/_form', ['submit_text' => 'Create Glossary'])
	{!! Form::close() !!}
@endsection
