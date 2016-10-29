<!-- /resources/views/users/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li><a href="{!! url('/users'); !!}">Users</a></li>
	<li><a href="{{ route('users.show', $user->id) }}">{{ $user->firstname }} {{ $user->lastname }}</a></li>
	<li class="active">User activities</li>
	</ul>

	<h2>Activities</h2>
	<h4>This is an overview of the user activities</h4>

	@if ( !$user->activities->count() )
		No activities found in the database!<br><br>
		@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Time</td>
		<td class="header">Description</td>
		</tr>

		@foreach( $user->activities as $activity )
			<tr>
			<td>{{ $activity->created_at }}</td>
			<td>{{ $activity->description }}</td>
			</tr>
		@endforeach

		</table>
	@endif

@endsection
