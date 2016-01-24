<!-- /resources/views/auth/password.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Password reset</li>
	</ul>

	<h2>Password reset</h2>
	<h4>Use the form below to reset your password</h4>

	<form method="POST" action="{!! url('/'); !!}/password/email">
		{!! csrf_field() !!}

		@if (count($errors) > 0)
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		@endif

		<div class="form-group">
		  <label for="usr">Email:</label>
		  <input type="email" name="email" value="{{ old('email') }}" style="width:550px;" class="form-control">
		</div>

		<div>
			<button class="btn btn-primary" type="submit">
				Send Password Reset Link
			</button>
		</div>
	</form>

@endsection
