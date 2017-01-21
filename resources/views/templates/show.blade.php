<!-- /resources/views/template/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
		<li><a href="{!! url('/'); !!}">Home</a></li>
		@if ($template->section->subject->parent)
			<li><a href="{{ route('subjects.show', $template->section->subject->parent) }}">{{ $template->section->subject->parent->subject_name }}</a></li>
		@endif
		<li><a href="{{ route('subjects.show', $template->section->subject) }}">{{ $template->section->subject->subject_name }}</a></li>
		<li><a href="{{ route('subjects.sections.show', array($template->section->subject, $template->section)) }}">{{ $template->section->section_name }}</a></li>
		@if ( $template->parent )
			<li><a href="{{ route('subjects.sections.templates.show', array($template->section->subject, $template->section, $template->parent)) }}">{{ $template->parent->template_name }}</a></li>
		@endif
		<li class="active">{{ $template->template_name }}</li>
	</ul>

	@if ( $template->children->count() )
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Template</td>
		<td class="header">Short description</td>
		<td class="header" style="width: 245px;">Options</td>
		</tr>
		@foreach( $template->children as $child )
			@if ($child->visible == "False")
				<tr class="notvisible">
			@else
				<tr>
			@endif
			{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('subjects.sections.templates.destroy', $template->section->subject, $template->section, $child), 'onsubmit' => 'return confirm(\'Are you sure to delete this template?\')')) !!}
			<td><a href="{{ route('subjects.sections.templates.show', [$template->section->subject, $template->section, $child]) }}">{{ $child->template_name }}</a></td>
			<td>{!! html_entity_decode(e($child->template_shortdesc)) !!}</td>
			<td>
			@if ( $child->rows->count() && $child->columns->count() )
				<a class="btn btn-primary btn-xs" style="margin-left:2px;" href="{{ url('exporttemplate') . '/' . $child->id }}">Export</a>
			@endif
			@can('update-section', $template->section)
				{!! link_to_route('subjects.sections.templates.edit', 'Edit', array($template->section->subject, $template->section, $child), array('class' => 'btn btn-info btn-xs')) !!}
				@if ( $child->rows->count() && $child->columns->count() )
					<a class="btn btn-warning btn-xs" style="margin-left:2px;" href="{{ url('templatestructure') . '/' . $child->id }}">Structure</a>
				@endif
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:2px;')) !!}
			@endcan
			</td>
			{!! Form::close() !!}
			</tr>
		@endforeach
		</table>
	@endif

	<h2>{{ $template->template_name }}</h2>
	<h4 class="tinymce" title="{{ $template->template_name }}">{!! html_entity_decode(e($template->template_shortdesc)) !!}</h4>
	<h4 class="tinymce" title="{{ $template->template_name }}">{!! html_entity_decode(e($template->template_longdesc)) !!}</h4>
	@if ( $template->rows->count() && $template->columns->count() )
		<h4>{!! App\Helper::contentAdjust(nl2br(e($template->frequency_description))) !!}</h4>
		<h4>{!! App\Helper::contentAdjust(nl2br(e($template->reporting_dates_description))) !!}</h4>
		<h4>{!! App\Helper::contentAdjust(nl2br(e($template->main_changes_description))) !!}</h4>
		<h4>{!! App\Helper::contentAdjust(nl2br(e($template->links_other_temp_description))) !!}</h4>
		<h4>{!! App\Helper::contentAdjust(nl2br(e($template->process_and_organisation_description))) !!}</h4>
		<h5><a href="{{ url('subjects') . '/' . $template->section->subject->id . '/sections/' . $template->section->id . '/templates/' . $template->id . '/manual' }}"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print manual</a></h5>
	@endif

	@if ( $descriptions->count() )
		<div class="info-group" style="margin-bottom:10px;">
		<div class="accordion-heading"><a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseSix">Show reference table {{ $technicaltype->type_name }}</a></div>
		<div id="collapseSix" class="accordion-body collapse" style="height: 0px;"><div class="accordion-inner">
		<br>
		<table style="width:50%;" class="table dialog table-striped" border="1">
		<tr class="warning">
		<td class="header">Value</td>
		<td class="header">Description</td>
		</tr>
		@foreach( $descriptions as $description )
			</tr>
			<td>{{ $description->content }}</td>
			<td>{{ $description->description }}</td>
			</tr>
		@endforeach
		</table><br></div></div></div>
	@endif

	@if ( $template->rows->count() && $template->columns->count() )
		<!-- Hidden div to carry template id -->
		<div class="templateId" id="{{ $template->id }}" style="display: none;"></div>

		<table class="table table-bordered template" border="1">

		<!-- Table header with column names -->
		<thead>
		<tr class="success">

		<td class="header content">Row#</td>
		<td class="header">
		@if ($template->row_header_desc)
			{{ $template->row_header_desc }}
		@else
			Row description
		@endif
		</td>
		{{-- Compare if there are any references used, if not equal show reference column --}}
		@if ( $emptyReferences->count() <> $template->rows->count() )
			<td class="header">Reference</td>
		@endif

		@foreach( $template->columns as $column )
			<td class="content header" id="$column->column_code">
			{{ $column->column_description }}
			</td>
		@endforeach
		</tr>

		<!-- Table header with column nums -->
		<tr class="header2">

		<td></td>
		<td></td>
		{{-- Compare if there are any references used, if not equal show reference column --}}
		@if ( $emptyReferences->count() <> $template->rows->count() )
			<td></td>
		@endif

		@foreach( $template->columns as $column )
			<td class="column">
			{{ $column->column_code }}
			</td>
		@endforeach

		</tr>
		</thead>

		<!-- Table content with row information -->
		<tbody>
		@foreach( $template->rows as $row )

			<tr>
			<td class="desc">{{ $row->row_code }}</td>
			<td class="desc property{{ $row->row_property }}">{{ $row->row_description }}</td>
			{{-- Compare if there are any references used, if not equal show reference column --}}
			@if ( $emptyReferences->count() <> $template->rows->count() )
				<td class="desc property{{ $row->reference }}">{{ $row->row_reference }}</td>
			@endif
			<!-- Table cell information, column and row combination -->
			@foreach( $template->columns as $column )

				<!-- Create a new variable, column and row combination -->
				{{--*/ $field = 'column' . $column->column_code . '-row' . $row->row_code /*--}}

				@if (array_key_exists($field, $disabledFields))
					<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="disabled" id="{{ $field }}"></td>
				@else
					@if (strpos($field,$searchvalue) !== false)
						@if ($template->template_type == 'non-clickable')
							<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="non-clickable highlight" id="{{ $field }}">
						@else
							<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="tablecell highlight" id="{{ $field }}">
						@endif
						@if (array_key_exists($field, $propertyFields))
							{{ $propertyFields[$field] }}
						@endif
						</td>
					@else
						@if ($template->template_type == 'non-clickable')
							<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="non-clickable" id="{{ $field }}">
						@else
							<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="tablecell" id="{{ $field }}">
						@endif
						@if (array_key_exists($field, $propertyFields))
							{{ $propertyFields[$field] }}
						@endif
						</td>
					@endif
				@endif

			@endforeach
			</tr>

		@endforeach
		</tbody>

		</table>

	@endif

	@if ( $template->parent )
		<p>{!! link_to_route('subjects.sections.templates.show', 'Back to template', array($template->section->subject, $template->section, $template->parent))  !!}</p>
	@else
		<p>{!! link_to_route('subjects.sections.show', 'Back to Section', array($template->section->subject, $template->section))  !!}</p>
	@endif

@include('templates.modal')

@endsection
