<!-- /resources/views/subjects/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Building Blocks</li>
	</ul>

	<h2>Building Blocks</h2>
	<h4>Please make a selection of one of the following building blocks</h4>

	@if ( !$subjects->count() )
		No building blocks found in the database!<br><br>
	@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		<td class="header">Description</td>
		<td class="header">Parent</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $subjects as $subject )
			<tr>
			<td>{{ $subject->subject_name }}</td>
			<td>{{ $subject->subject_description }}</td>
			<td>
			@if ($subject->parent)
				{{ $subject->parent->subject_name }}
			@endif
			</td>
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('subjects.destroy', $subject->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this building block?\')')) !!}
			<td>
			{!! link_to_route('subjects.edit', 'Edit', array($subject->id), array('class' => 'btn btn-info btn-xs')) !!}
			{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach

		</table>
	@endif

	<p>
	{!! link_to_route('subjects.create', 'Create Building block') !!}
	</p>

@endsection
