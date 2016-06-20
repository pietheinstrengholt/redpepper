<!-- /resources/views/glossaries/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Glossaries</li>
	</ul>

	<h2>Glossaries</h2>
	<h4>Please make a selection of one of the following glossaries</h4>

	@if ( !$glossaries->count() )
		No glossaries found in the database!<br><br>
		@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $glossaries as $glossary )
			<tr>
			<td>{{ $glossary->glossary_name }}</td>
			<td>{{ $glossary->glossary_description }}</td>
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('glossaries.destroy', $glossary->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this glossary?\')')) !!}
			<td>
			{!! link_to_route('glossaries.edit', 'Edit', array($glossary->id), array('class' => 'btn btn-info btn-xs')) !!}
			{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach

		</table>
	@endif

	<p>
	{!! link_to_route('glossaries.create', 'Create Glossary') !!}
	</p>

@endsection
