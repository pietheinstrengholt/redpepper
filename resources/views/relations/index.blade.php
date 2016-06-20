<!-- /resources/views/relations/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Relations</li>
	</ul>

	<h2>Relations</h2>
	<h4>Please make a selection of one of the following Relations</h4>

	@if ( !$relations->count() )
		No relations found in the database!<br><br>
		@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $relations as $relation )
			<tr>
			<td>{{ $relation->relation_name }}</td>
			<td>{{ $relation->relation_description }}</td>
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('relations.destroy', $relation->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this relation type?\')')) !!}
			<td>
			{!! link_to_route('relations.edit', 'Edit', array($relation->id), array('class' => 'btn btn-info btn-xs')) !!}
			{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach

		</table>
	@endif

	<p>
	{!! link_to_route('relations.create', 'Create Relation') !!}
	</p>

@endsection
