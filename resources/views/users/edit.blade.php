<!-- /resources/views/users/edit.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/users'); !!}">Users</a></li>
	  <li class="active">{{ $user->username }}</li>
	</ul>

	<h2>Edit User "{{ $user->username }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::model($user, ['method' => 'PATCH', 'route' => ['users.update', $user->id]]) !!}
	@include('users/partials/_form', ['submit_text' => 'Edit User'])
	{!! Form::close() !!}
@endsection
