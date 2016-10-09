<!-- /resources/views/sections/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	@if ( $subject )
		<li class="active">{{ $subject->subject_name }}</li>
	@else
		<li class="active">Sections</li>
	@endif
	</ul>

	@if ( $subject )
		<h2>{{ $subject->subject_name }}</h2>
		<h3>{!! html_entity_decode(e($subject->subject_description)) !!}</h3>
		<h4>{!! html_entity_decode(e($subject->subject_longdesc)) !!}</h4>
	@else
		<h2>Sections</h2>
	@endif
	<h4>Please make a selection of one of the following sections</h4>

	@if ( !$sections->count() )
		No sections found in the database!<br><br>
	@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Name</td>
		@if ( !$subject )
			<td class="header">Subject</td>
		@endif
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
			@if ( !$subject )
				<td>
				@if (!empty($section->subject))
					{{ $section->subject->subject_name }}
				@endif
				</td>
			@endif
			<td>{!! html_entity_decode(e($section->section_description)) !!}</td>
			<td>
			@can('superadmin', $section)
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('sections.destroy', $section->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this section?\')')) !!}
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			@endcan
			@can('update-section', $section)
				{!! link_to_route('sections.edit', 'Edit', array($section->id), array('class' => 'btn btn-info btn-xs')) !!}
			@endcan
			{!! Form::close() !!}
			</td>
			</tr>
		@endforeach

		</table>
	@endif

	@if (!Auth::guest())
		<p>
		@if ( $subject )
			{!! link_to_route('sections.create', 'Create Section', array('subject_id' => $subject->id))  !!}
		@else
			{!! link_to_route('sections.create', 'Create Section')  !!}
		@endif
		</p>
	@endif

@endsection
