<!-- /resources/views/sections/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
		<li><a href="{!! url('/'); !!}">Home</a></li>
		@if ( $section->subject )
			<li><a href="{!! url('/sections?subject_id=' . $section->subject->id); !!}">{{ $section->subject->subject_name }}</a></li>
		@else
			<li><a href="{!! url('/sections'); !!}">Sections</a></li>
		@endif
		<li class="active">{{ $section->section_name }}</li>
	</ul>

	<h2>{{ $section->section_name }}</h2>

	@if ( !$section->templates->count() )
		<p>This section has no items.</p><br>
	@else
		<h4 class="tinymce">{!! html_entity_decode(e($section->section_description)) !!}</h4>
		<h4 class="tinymce">{!! html_entity_decode(e($section->section_longdesc)) !!}</h4>
		<h5>Total overview of all items</h5>

		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template</td>
		<td class="header">Short description</td>
		<td class="header" style="width: 245px;">Options</td>
		</tr>
		@foreach( $templates as $template )
			@if ($template->visible == "False")
				<tr class="notvisible">
			@else
				<tr>
			@endif
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('sections.templates.destroy', $template->section_id, $template->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this template?\')')) !!}
			<td><a href="{{ route('sections.templates.show', [$section->id, $template->id]) }}">{{ $template->template_name }}</a></td>
			<td>{!! html_entity_decode(e($template->template_shortdesc)) !!}</td>
			<td>
			@if ( $template->rows->count() && $template->columns->count() )
				<a class="btn btn-primary btn-xs" style="margin-left:2px;" href="{{ url('exporttemplate') . '/' . $template->id }}">Export</a>
			@endif
			@can('update-section', $section)
				{!! link_to_route('sections.templates.edit', 'Edit', array($template->section_id, $template->id), array('class' => 'btn btn-info btn-xs')) !!}
				@if ( $template->rows->count() && $template->columns->count() )
					<a class="btn btn-warning btn-xs" style="margin-left:2px;" href="{{ url('templatestructure') . '/' . $template->id }}">Structure</a>
				@endif
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:2px;')) !!}
			@endcan
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach
		</table>
	@endif

	<p>
	{!! link_to_route('sections.index', 'Back to Sections') !!}
	@can('update-section', $section)
		| {!! link_to_route('sections.templates.create', 'Create Template', $section->id) !!}
	@endcan
	</p>

@endsection
