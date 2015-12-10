<!-- /resources/views/sections/index.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Logs</h2>
	<h4>The table below represents an overview of all user activities</h4>
 
    @if ( !$logs->count() )
        No logs have been found in the database!<br><br>
    @else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Action type</td>
		<td class="header">Description</td>
		<td class="header" style="width: 20%;">User</td>
		</tr>
		
		@foreach( $logs as $log )
		<tr>
		<td>{{ $log->content }}</a></td>
		<td>{{ $log->description }}</a></td>
		<td>{{ $log->user->firstname }} {{ $log->user->lastname }} ({{ $log->user->username }})</a></td>
		</tr>
		@endforeach

		</table>
    @endif
	
@endsection

@stop