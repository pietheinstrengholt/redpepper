<!-- /resources/views/auth/show.blade.php -->
@extends('layouts.master')

@section('content')

	<h2>Login</h2><hr>
	<h4>Please use the login form below</h4>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<strong>Whoops!</strong> There were some problems with your input.<br><br>
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<form method="POST" action="<?php echo url('/auth/login'); ?>">
		{!! csrf_field() !!}

		<div class="form-group">
			<label for="caption">Username</label>
			<input type="text" class="form-control" name="username" value="">
		</div>

		<div class="form-group">
			<label for="description">Password</label>
			<input type="password" class="form-control" name="password" value="">
		</div>

		<div style="padding-top: 10px;" class="form-group">
			<button style="margin-bottom: 3px;" class="btn btn-primary" type="submit"><span class="glyphicon glyphicon-user" aria-hidden="true"></span> Login</button> <a style="margin-left:15px;" href="{{ URL::to('/password/email') }}">Forgot your password?</a>
		</div>
	</form>


@endsection
