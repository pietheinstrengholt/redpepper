<!-- /resources/views/departments/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Departments</li>
	</ul>

	<h2>Departments</h2>
	<h4>Please make a selection of one of the following departments</h4>

	@if ( !$departments->count() )
		No departments found in the database!<br><br>
	@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $departments as $department )
			<tr>
			<td>{{ $department->department_name }}</td>
			<td>{{ $department->department_description }}</td>
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('departments.destroy', $department->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this department?\')')) !!}
			<td>
			{!! link_to_route('departments.edit', 'Edit', array($department->id), array('class' => 'btn btn-info btn-xs')) !!}
			{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach

		</table>
	@endif

	<p>
	{!! link_to_route('departments.create', 'Create Department') !!}
	</p>

@endsection
