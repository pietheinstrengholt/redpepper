<!-- /resources/views/departments/create.blade.php -->
@extends('layouts.master')

@section('content')
  <h2>Create Department</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

  {!! Form::model(new App\Department, ['route' => ['departments.store']]) !!}
  @include('departments/partials/_form', ['submit_text' => 'Create Department'])
  {!! Form::close() !!}
@endsection

@stop
