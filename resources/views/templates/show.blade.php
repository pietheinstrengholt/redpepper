<!-- /resources/views/template/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/sections'); !!}">Sections</a></li>
	  <li><a href="{!! url('/sections/' . $template->section->id); !!}">{{ $template->section->section_name }}</a></li>
	  <li class="active">{{ $template->template_name }}</li>
	</ul>

    <h2>{{ $template->template_name }}</h2>
	<h4>{{ $template->template_shortdesc }}</h4>
	<h4>{{ $template->template_longdesc }}</h4>
	<h5><a href="{!! url('/templatemanual/' . $template->id); !!}"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print manual</a></h5>
	
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
	
	<!-- Hidden div to carry template id -->
	<div class="templateId" id="{{ $template->id }}" style="display: none;"></div>

	@if ( !$template->columns->count() || !$template->rows->count() )
		Error: This template has no columns or no rows.
	@else
		<table class="table table-bordered template" border="1">

		<!-- Table header with column names -->
		<thead>
		<tr class="success">

		<td class="header content">Row#</td>
		<td class="header">Row description</td>
		<td class="header">Reference</td>

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
		<td></td>

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
			<td class="desc property{{ $row->reference }}">{{ $row->row_reference }}</td>
			<!-- Table cell information, column and row combination -->
			@foreach( $template->columns as $column )

				<!-- Create a new variable, column and row combination -->
				{{--*/ $field = 'column' . $column->column_code . '-row' . $row->row_code /*--}}

				@if (array_key_exists($field, $disabledFields))
					<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="disabled" id="{{ $field }}"></td>
				@else
					@if (strpos($field,$searchvalue) !== false)
						<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="tablecell highlight" id="{{ $field }}">
							@if (array_key_exists($field, $propertyFields))
								{{ $propertyFields[$field] }}
							@endif
						</td>
					@else
						<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="tablecell" id="{{ $field }}">
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

	<p>
	<a href="{!! url('/sections/' . $template->section->id); !!}">Back to Sections</a>
	</p>

@include('templates.modal')

@endsection