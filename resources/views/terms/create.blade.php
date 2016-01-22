<!-- /resources/views/terms/create.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>Create Term</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model(new App\Term, ['route' => ['terms.store']]) !!}
	@include('terms/partials/_form', ['submit_text' => 'Create Term'])
	{!! Form::close() !!}
@endsection
