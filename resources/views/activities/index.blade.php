<!-- /resources/views/activities/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Activities</li>
	</ul>

	<h2>Activities</h2>
	<h4>This is an overview of all user activities</h4>

	@if ( !$activities->count() )
		No activities found in the database!<br><br>
		@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Time</td>
		<td class="header">Description</td>
		<td class="header">User</td>
		</tr>

		@foreach( $activities as $activity )
			<tr>
			<td>{{ $activity->created_at }}</td>
			<td>{{ $activity->description }}</td>
			<td><a href="{{ route('users.show', $activity->creator->id) }}">{{ $activity->creator->firstname }} {{ $activity->creator->lastname }}</a></td>
			</tr>
		@endforeach

		</table>
	@endif

	<div class="text-center">
		{{ $activities->links() }}
	</div>

@endsection
