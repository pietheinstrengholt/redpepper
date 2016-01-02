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
		<td class="header">Event type</td>
		<td class="header">Action</td>		
		<td class="header">Changerequest id</td>
		<td class="header">Section name</td>
		<td class="header">Template name</td>
		<td class="header">Username id</td>
		<td class="header" style="width: 20%;">User</td>
		<td class="header">Created</td>
		</tr>

		@foreach( $logs as $log )
			<tr>
			<td>{{ $log->log_event }}</td>
			<td>{{ $log->action }}</td>
			<td>{{ $log->changerequest_id }}</a></td>
			
			<td>
			@if ($log->section_id)
				@if ($log->section)
					{{ $log->section->section_name }}
				@else
					{{ $log->section_id }}		
				@endif
			@endif
			</td>

			<td>
			@if ($log->template_id)
				@if ($log->template)
					{{ $log->template->template_name }}
				@else
					{{ $log->template_id }}
				@endif
			@endif
			</td>

			<td>{{ $log->username_id }}</td>
			<td>{{ $log->user->firstname }} {{ $log->user->lastname }} ({{ $log->user->username }})</td>
			<td>{{ $log->created_at }}</td>
			</tr>
		@endforeach

		</table>
	@endif
	
	<div class="pagination">
		{!! $logs->render() !!}
	</div>	

@endsection
