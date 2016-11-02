<!-- /resources/views/users/editrights.blade.php -->
@extends('layouts.master')

@section('content')

	<div class="form-horizontal">

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

	{!! Form::open(array('action' => 'UserController@updaterights', 'id' => 'form')) !!}
	<input name="username_id" type="hidden" value="{{ $user->id }}"/>
	<div class="form-group">
	{!! Form::label('role', 'Role:', array('class' => 'col-sm-3 control-label')) !!}
	<div class="col-sm-6">
	<select name="role" class="form-control" class="form-control" id="role">
	@foreach( $roles as $role )
		@if ( $user->role == $role) {
			<option selected="selected">{{ $role }}</option>
		@else
			<option>{{ $role }}</option>
		@endif
	@endforeach
	</select>
	</div>
	</div>

	@if ( $subjects->count() )
		<div class="form-group">
		<table class="table table-striped table-condensed table-bordered">
		<tr class="success">
		<th><h4>Section or group</h4></th>
		<th style="text-align: center;"><h4>Selected rights</h4></th>
		</tr>
		<tr class="allrights notvisible">
		<td><strong>All</strong></td>
		<td style="text-align: center;"><input type="checkbox"></td>
		</tr>
		@foreach( $subjects as $subject )
			<tr class="master" id="{{ $subject->id }}" style="background-color:#f9f9f9;">
			<td><strong>{{ $subject->subject_name }}</strong></td>
			@if ( in_array($subject->id, $subjectrights) )
				<td class="rights" style="text-align: center;"><input name="subject[{{ $subject->id }}]" id="subject_rights" checked type="checkbox" value="{{ $subject->id }}"></td>
			@else
				<td class="rights" style="text-align: center;"><input name="subject[{{ $subject->id }}]" id="subject_rights" type="checkbox" value="{{ $subject->id }}"></td>
			@endif
			</tr>
			@foreach( $subject->sections as $section )
				<tr class="slave" id="{{ $subject->id }}" style="background-color:#e0e0e0;">
				<td>
				@if ($section->subject)
					<strong style="padding-left:20px;">{{ $section->section_name }}</strong>
				@endif
				</td>
				@if ( in_array($section->id, $sectionrights) )
					<td class="rights" style="text-align: center;"><input name="section[{{ $section->id }}]" id="subject_rights" checked type="checkbox" value="{{ $section->id }}"></td>
				@else
					<td class="rights" style="text-align: center;"><input name="section[{{ $section->id }}]" id="subject_rights" type="checkbox" value="{{ $section->id }}"></td>
				@endif
				</tr>
			@endforeach
		@endforeach
		</table>
		</div>
	@endif

	<button type="submit" class="btn btn-warning">Submit rights</button>
	{!! Form::close() !!}

	</div>

@endsection
