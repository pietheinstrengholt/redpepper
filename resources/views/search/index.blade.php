<!-- /resources/views/search/index.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>Search results</h2>
	<h4>the following search results have been found</h4>

	@if ( $rows->count() )
		<table class="table search-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row code</td>
		<td class="header">Description</td>
		</tr>

		@foreach( $rows as $row )
			<tr>
			<td><a href="{!! url('sections/' . $row->template->section_id . '/templates/' . $row->template_id . '?row=' . $row->row_code); !!}">{{ $row->template->template_name }}</a></td>
			<td>{{ $row->row_code }}</td>
			<td>{{ $row->row_description }}</td>
			</tr>
		@endforeach

		</table>
	@endif

	@if ( $columns->count() )
		<table class="table search-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Column code</td>
		<td class="header">Description</td>
		</tr>

		@foreach( $columns as $column )
			<tr>
			<td><a href="{!! url('sections/' . $column->template->section_id . '/templates/' . $column->template_id . '?column=' . $column->column_code); !!}">{{ $column->template->template_name }}</a></td>
			<td>{{ $column->column_code }}</td>
			<td>{{ $column->column_description }}</td>
			</tr>
		@endforeach

		</table>
	@endif

	@if ( $content->count() )
		<table class="table search-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row code</td>
		<td class="header">Column code</td>
		<td class="header">Content Type</td>
		<td class="header">Content</td>
		</tr>

		@foreach( $content as $requirement )
			<tr>
			<td><a href="{!! url('sections/' . $requirement->template->section_id . '/templates/' . $requirement->template_id . '?row=' . $requirement->row_code . '&column=' . $requirement->column_code); !!}">{{ $requirement->template->template_name }}</a></td>
			<td>{{ $requirement->row_code }}</td>
			<td>{{ $requirement->column_code }}</td>
			<td>{{ $requirement->content_type }}</td>
			<td>{{ $requirement->content }}</td>
			</tr>
		@endforeach

		</table>
	@endif

	@if ( $technicals->count() )
		<table class="table search-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row code</td>
		<td class="header">Column code</td>
		<td class="header">Source</td>
		<td class="header">Type</td>
		<td class="header">Content</td>
		<td class="header">Description</td>
		</tr>

		@foreach( $technicals as $technical )
			<tr>
			<td><a href="{!! url('sections/' . $technical->template->section_id . '/templates/' . $technical->template_id . '?row=' . $technical->row_code . '&column=' . $technical->column_code); !!}">{{ $technical->template->template_name }}</a></td>
			<td>{{ $technical->row_code }}</td>
			<td>{{ $technical->column_code }}</td>
			<td>{{ $technical->source->source_name }}</td>
			<td>{{ $technical->type->type_name }}</td>
			<td>{{ $technical->content }}</td>
			<td>{{ $technical->description }}</td>
			</tr>
		@endforeach

		</table>
	@endif

	@if ( !$rows->count() && !$columns->count() && !$content->count() && !$technicals->count() )
		<p>No content has been found, try to search again!</p>
	@endif

@endsection
