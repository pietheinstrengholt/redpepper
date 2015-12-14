<!-- /resources/views/sections/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Sections</li>
	</ul>

	<h2>Sections</h2>
	<h4>Please make a selection of one of the following templates</h4>

	@if ( !$sections->count() )
		No sections found in the database!<br><br>
	@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $sections as $section )
			@if ($section->visible == "False")
				<tr class="notvisible">
			@else
				<tr>
			@endif
			<td><a href="{{ route('sections.show', $section->id) }}">{{ $section->section_name }}</a></td>
			<td>{{ $section->section_description }}</td>
			<td>
			@if (!Auth::guest())
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('sections.destroy', $section->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this section?\')')) !!}
				{!! link_to_route('sections.edit', 'Edit', array($section->id), array('class' => 'btn btn-info btn-xs')) !!}
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
				{!! Form::close() !!}
			@endif
			</td>
			</tr>
		@endforeach

		</table>
	@endif

	<p>
	{!! link_to_route('sections.create', 'Create Section') !!}
	</p>

@endsection

@stop
