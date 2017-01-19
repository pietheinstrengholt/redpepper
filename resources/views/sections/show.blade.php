<!-- /resources/views/sections/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
		<li><a href="{!! url('/'); !!}">Home</a></li>
		@if ($section->subject->parent)
			<li><a href="{{ route('subjects.show', $section->subject->parent->id) }}">{{ $section->subject->parent->subject_name }}</a></li>
		@endif
		<li><a href="{{ route('subjects.show', $section->subject->id) }}">{{ $section->subject->subject_name }}</a></li>
		<li class="active">{{ $section->section_name }}</li>
	</ul>

	<h4 class="tinymce">{!! $section->section_description !!}</h4>
	<h4 class="tinymce">{!! $section->section_longdesc !!}</h4>

	@if ( !$section->templates->count() )
		<p>This section has no items.</p><br>
	@else
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
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('subjects.sections.templates.destroy', $subject->id, $section->id, $template->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this template?\')')) !!}
			<td><a href="{{ route('subjects.sections.templates.show', [$subject->id, $section->id, $template->id]) }}">{{ $template->template_name }}</a></td>
			<td>{!! html_entity_decode(e($template->template_shortdesc)) !!}</td>
			<td>
			@if ( $template->rows->count() && $template->columns->count() )
				<a class="btn btn-primary btn-xs" style="margin-left:2px;" href="{{ url('exporttemplate') . '/' . $template->id }}">Export</a>
			@endif
			@can('superadmin')
				{!! link_to_route('subjects.sections.templates.edit', 'Edit', array($subject->id, $section->id, $template->id), array('class' => 'btn btn-info btn-xs')) !!}
				@if ( $template->rows->count() && $template->columns->count() )
					<a class="btn btn-warning btn-xs" style="margin-left:2px;" href="{{ url('subjects') . '/' . $template->section->subject->id . '/sections/' . $template->section->id . '/templates/' . $template->id . '/structure' }}">Structure</a>
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
	{!! link_to_route('subjects.show', 'Back to building block', $subject) !!}
	@can('update-section', $section)
		| {!! link_to_route('subjects.sections.templates.create', 'Create Template', array($subject->id, $section->id)) !!}
	@endcan
	@if ( $files->count() )
		</p>
	@endif

	@if ( $files->count() )
		<br>
		<h4>The following files are relevant for this section</h4>
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header" style="width:30%;">File name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $files as $file )
			<tr>
			<td><a target="_blank" href="{{ URL::asset('/files') }}/{{ $section->id }}/{{ $file->file_name }}">{{ $file->file_name }}</a></td>
			<td>{{ $file->file_description }}</td>
			@can('update-section', $section)
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('fileupload.destroy', $file->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this file?\')')) !!}
				<td>
				{!! link_to_route('fileupload.edit', 'Edit', array($file->id), array('class' => 'btn btn-info btn-xs')) !!}
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
				</td>
				{!! Form::close() !!}
			@else
				<td></td>
			@endcan
			</tr>
		@endforeach

		</table>
	@endif

	@can('update-section', $section)
		@if ( $files->count() )
			<p>{!! link_to_route('fileupload.create', 'Upload new file', array('section_id' => $section->id))  !!}</p>
		@else
			| {!! link_to_route('fileupload.create', 'Upload new file', array('section_id' => $section->id))  !!}
		@endif
	@endcan

@endsection
