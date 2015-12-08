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
			<td><a href="<?php echo url('sections/' . $row->template->section_id . '/templates/' . $row->template_id . '?row=' . $row->row_code); ?>">{{ $row->template->template_name }}</a></td>
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
			<td><a href="<?php echo url('sections/' . $column->template->section_id . '/templates/' . $column->template_id . '?column=' . $column->column_code); ?>">{{ $column->template->template_name }}</a></td>
			<td>{{ $column->column_code }}</td>
			<td>{{ $column->column_description }}</td>
			</tr>
		@endforeach

		</table>
    @endif	
	
    @if ( $fields->count() )
		<table class="table search-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row code</td>
		<td class="header">Column code</td>
		<td class="header">Property</td>
		<td class="header">Content</td>
		</tr>
		
		@foreach( $fields as $field )
			<tr>
			<td><a href="<?php echo url('sections/' . $field->template->section_id . '/templates/' . $field->template_id . '?row=' . $field->row_code . '&column=' . $field->column_code); ?>">{{ $field->template->template_name }}</a></td>
			<td>{{ $field->row_code }}</td>
			<td>{{ $field->column_code }}</td>
			<td>{{ $field->property }}</td>
			<td>{{ $field->content }}</td>
			</tr>
		@endforeach

		</table>
    @endif

    @if ( $requirements->count() )
		<table class="table search-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Field id</td>
		<td class="header">Content Type</td>
		<td class="header">Content</td>
		</tr>
		
		@foreach( $requirements as $requirement )
			<tr>
			<td><a href="<?php echo url('sections/' . $requirement->template->section_id . '/templates/' . $requirement->template_id . '?field_id=' . $requirement->field_id); ?>">{{ $requirement->template->template_name }}</a></td>
			<td>{{ $requirement->field_id }}</td>
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
			<td><a href="<?php echo url('sections/' . $technical->template->section_id . '/templates/' . $technical->template_id . '?row=' . $technical->row_code . '&column=' . $technical->column_code); ?>">{{ $technical->template->template_name }}</a></td>
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
	
	@if ( !$rows->count() && !$columns->count() && !$fields->count() && !$requirements->count() && !$technicals->count() )
		<p>No content has been found, try to search again!</p>
    @endif	
	
@endsection

@stop