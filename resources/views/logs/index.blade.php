<!-- /resources/views/sections/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">User activities</li>
	</ul>

	<h2>Logs</h2>
	<h4>The table below represents an overview of all user activities</h4>

	@if ( !$logs->count() )
		No logs have been found in the database!<br><br>
	@else
		<table class="table section-table dialog table-striped table-condensed" border="1">

		<tr class="success">
		<td class="header">Content type</td>
		<td class="header">Action</td>		
		<td class="header">Content name</td>
		<td class="header" style="width: 20%;">User</td>
		<td class="header">Created</td>
		</tr>

		@foreach( $logs as $log )
			<tr>
			<td>{{ $log->content_type }}</a></td>
			<td>{{ $log->content_action }}</a></td>
			<td>{{ $log->content_name }}</a></td>			
			<td>{{ $log->user->firstname }} {{ $log->user->lastname }} ({{ $log->user->username }})</a></td>
			<td>{{ $log->created_at }}</a></td>
			</tr>
		@endforeach

		</table>
	@endif

@endsection
