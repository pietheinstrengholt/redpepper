<!-- /resources/views/sections/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	@if ($subject->parent)
		<li><a href="{{ route('subjects.show', $subject->parent->id) }}">{{ $subject->parent->subject_name }}</a></li>
	@endif
	<li class="active">{{ $subject->subject_name }}</li>
	</ul>

	<h2>{{ $subject->subject_name }}</h2>
	<h3 class="tinymce">{!! html_entity_decode(e($subject->subject_description)) !!}</h3>
	<h4 class="tinymce">{!! html_entity_decode(e($subject->subject_longdesc)) !!}</h4>

	@if ( $subject->children )
		<h4>This block has the following sub building blocks</h4>
		{{--*/ $buttons = array("btn-danger", "btn-primary", "btn-success", "btn-warning"); /*--}}

		{{--*/ $i=0; /*--}}
		{{--*/ $buttonvalue=0; /*--}}
		<div id="page-content1" class="row-flex row-flex-wrap row row-eq-height">
		@foreach ($subject->children as $key => $sub)
			<div class="col-md-3 col-sm-6 hero-feature">
				@if ($sub->visible == "False")
					<div class="well sub yellow">
				@else
					<div class="well sub">
				@endif
					<div class="caption" style="padding:0px;">
						<div class="clearfix"></div>
						<div class="content-container">
							<h3 class="center">
								<a href="{{ route('subjects.show', $sub->id) }}">{{ $sub->subject_name }}</a>
							</h3>
							<p class="center">{!! App\Helper::contentAdjust(nl2br(e($sub->subject_description))) !!}</p>
							<p class="p-more-info">
								<a href="{{ route('subjects.show', $sub->id) }}" class="btn {{ $buttons[$buttonvalue] }}">More info</a>
							</p>
							<div class="clearfix"></div>
						</div>
					</div>
				</div>
			</div>
			{{--*/ $i++; /*--}}
			{{--*/ $buttonvalue++; /*--}}
			@if ($i%4 == 0)
				{{--*/ $buttonvalue=0; /*--}}
				</div><div class="row">
			@endif
		@endforeach
		</div>
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
			<td>{!! link_to_route('subjects.sections.show', $section->section_name, array($subject, $section)) !!}</a></td>
			@if ( !$subject )
				<td>
				@if (!empty($section->subject))
					{{ $section->subject->subject_name }}
				@endif
				</td>
			@endif
			<td>{!! App\Helper::contentAdjust(nl2br(e($section->section_description))) !!}</td>
			<td>
			@can('update-section', $section)
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('subjects.sections.destroy', $subject, $section), 'onsubmit' => 'return confirm(\'Are you sure to delete this section?\')')) !!}
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
			@endcan
			@can('update-section', $section)
				{!! link_to_route('subjects.sections.edit', 'Edit', array($subject, $section), array('class' => 'btn btn-info btn-xs')) !!}
			@endcan
			{!! Form::close() !!}
			</td>
			</tr>
		@endforeach

		</table>
	@endif

	@if (Auth::check())
		@if (Auth::user()->can('update-subject', $subject))
			<p>
			{!! link_to_route('subjects.sections.create', 'Create Section', array($subject))  !!}
			</p>
		@endif
	@endif

@endsection
