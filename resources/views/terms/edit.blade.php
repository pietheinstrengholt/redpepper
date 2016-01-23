<!-- /resources/views/terms/edit.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>Edit Terms "{{ $term->term_name }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($term, ['method' => 'PATCH', 'route' => ['terms.update', $term->id]]) !!}
	@include('terms/partials/_form', ['submit_text' => 'Edit Term'])
	{!! Form::close() !!}
@endsection
