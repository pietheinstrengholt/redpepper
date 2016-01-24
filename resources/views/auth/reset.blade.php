<!-- /resources/views/auth/reset.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Password reset</li>
	</ul>

	<h2>Password reset</h2>
	<h4>Use the form below to reset your password</h4>

	<form method="POST" action="{!! url('/'); !!}/password/reset">
		{!! csrf_field() !!}
		<input type="hidden" name="token" value="{{ $token }}">

		@if (count($errors) > 0)
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		@endif
		
		<div class="form-group">
		  <label>Email:</label>
		  <input type="email" name="email" value="{{ old('email') }}" style="width:550px;" class="form-control">
		</div>
		
		<div class="form-group">
		  <label>Password:</label>
		  <input type="password" name="password"style="width:350px;" class="form-control">
		</div>
		
		<div class="form-group">
		  <label>Confirm Password:</label>
		  <input type="password" name="password_confirmation" style="width:350px;" class="form-control">
		</div>

		<div>
			<button class="btn btn-primary" type="submit">
				Reset Password
			</button>
		</div>
	</form>

@endsection
