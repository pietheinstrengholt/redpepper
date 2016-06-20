<!-- /resources/views/statuses/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Statuses</li>
	</ul>

	<h2>Statuses</h2>
	<h4>Please make a selection of one of the following statuses</h4>

	@if ( !$statuses->count() )
		No statuses found in the database!<br><br>
		@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $statuses as $status )
			<tr>
			<td>{{ $status->status_name }}</td>
			<td>{{ $status->status_description }}</td>
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('statuses.destroy', $status->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this status?\')')) !!}
			<td>
			{!! link_to_route('statuses.edit', 'Edit', array($status->id), array('class' => 'btn btn-info btn-xs')) !!}
			{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach

		</table>
	@endif

	<p>
	{!! link_to_route('statuses.create', 'Create Status') !!}
	</p>

@endsection
