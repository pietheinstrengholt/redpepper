<!-- /resources/views/users/index.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Users</h2>
	<h4>Please make a selection of one of the following users</h4>
 
    @if ( !$users->count() )
        No users found in the database!<br><br>
    @else
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Username</td>
		<td class="header">First name</td>
		<td class="header">Last name</td>
		<td class="header">E-mail address</td>
		<td class="header">Role</td>
		<td class="header">Department</td>
		<td class="header" style="width: 242px;">Options</td>
		</tr>
		
		@foreach( $users as $user )
		<tr>
		<td>{{ $user->username }}</td>
		<td>{{ $user->firstname }}</td>
		<td>{{ $user->lastname }}</td>
		<td>{{ $user->email }}</td>
		<td>{{ $user->role }}</td>
		<td>{{ $user->department->department_name }}</td>
		{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('users.destroy', $user->id))) !!}
		<td>
			{!! link_to_route('users.edit', 'Edit', array($user->id), array('class' => 'btn btn-info btn-xs')) !!}
			<a class="btn btn-warning btn-xs" style="margin-left:2px;" href="{{ url('users') . '/' . $user->id . '/rights' }}">Rights</a>
			<a class="btn btn-warning btn-xs" style="margin-left:2px;" href="{{ url('users') . '/' . $user->id . '/password' }}">Password</a>
			{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
		</td>
		{!! Form::close() !!}
		</tr>
		@endforeach

		</table>
    @endif
	
@endsection

@stop