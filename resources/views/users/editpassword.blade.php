<!-- /resources/views/users/editpassword.blade.php -->
@extends('layouts.master')

@section('content')

	<div class="form-horizontal">

	<h2>Reset password for user "{{ $user->username }}"</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'UserController@updatepassword', 'id' => 'form')) !!}

	<div class="form-group">
	<label class="col-md-4 control-label">Password</label>
	<div class="col-md-6">
	<input type="password" class="form-control" name="password">
	</div>
	</div>

	<div class="form-group">
	<label class="col-md-4 control-label">Confirm Password</label>
	<div class="col-md-6">
	<input type="password" class="form-control" name="password_confirmation">
	</div>
	</div>

	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	<input type="hidden" name="username_id" value="{!! $user->id !!}">

	<button type="submit" class="btn btn-warning">Submit new password</button>

	{!! Form::close() !!}

	</div>

@endsection