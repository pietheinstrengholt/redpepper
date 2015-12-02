<!-- /resources/views/search/index.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Search results</h2>
	<h4>the following search results have been found</h4>
 
    @if ( $rows->count() )
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row Name</td>
		<td class="header">Description</td>
		</tr>
		
		@foreach( $rows as $row )
			<tr>
			<td><a href="<?php echo url('sections/' . $row->template->section_id . '/templates/' . $row->template_id . '?row=' . $row->row_name); ?>">{{ $row->template->template_name }}</a></td>
			<td>{{ $row->row_name }}</td>
			<td>{{ $row->row_description }}</td>
			</tr>
		@endforeach

		</table>
    @endif
	
    @if ( $columns->count() )
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Column Name</td>
		<td class="header">Description</td>
		</tr>
		
		@foreach( $columns as $column )
			<tr>
			<td><a href="<?php echo url('sections/' . $column->template->section_id . '/templates/' . $column->template_id . '?column=' . $column->column_name); ?>">{{ $column->template->template_name }}</a></td>
			<td>{{ $column->column_name }}</td>
			<td>{{ $column->column_description }}</td>
			</tr>
		@endforeach

		</table>
    @endif	
	
    @if ( $fields->count() )
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row Name</td>
		<td class="header">Column Name</td>
		<td class="header">Property</td>
		<td class="header">Content</td>
		</tr>
		
		@foreach( $fields as $field )
			<tr>
			<td><a href="<?php echo url('sections/' . $field->template->section_id . '/templates/' . $field->template_id . '?row=' . $field->row_name . '&column=' . $field->column_name); ?>">{{ $field->template->template_name }}</a></td>
			<td>{{ $field->row_name }}</td>
			<td>{{ $field->column_name }}</td>
			<td>{{ $field->property }}</td>
			<td>{{ $field->content }}</td>
			</tr>
		@endforeach

		</table>
    @endif

    @if ( $requirements->count() )
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">

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
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template Name</td>
		<td class="header">Row name</td>
		<td class="header">Column name</td>
		<td class="header">Source</td>
		<td class="header">Type</td>
		<td class="header">Content</td>
		<td class="header">Description</td>
		</tr>
		
		@foreach( $technicals as $technical )
			<tr>
			<td><a href="<?php echo url('sections/' . $technical->template->section_id . '/templates/' . $technical->template_id . '?row=' . $technical->row_num . '&column=' . $technical->col_num); ?>">{{ $technical->template->template_name }}</a></td>
			<td>{{ $technical->row_num }}</td>
			<td>{{ $technical->col_num }}</td>
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