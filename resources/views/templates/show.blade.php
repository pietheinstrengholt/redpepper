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

		@foreach( $template->columns as $column )
			<td class="content header" id="$column->column_code" style="min-width: overflow-hidden; word-wrap: break-word;">
			{{ $column->column_description }}
			</td>
		@endforeach
		</tr>

		<!-- Table header with column nums -->

		<tr style="background-color: #EEE;">

		<td></td>
		<td></td>

		@foreach( $template->columns as $column )
			<td style="text-align: center; font-weight: bold;">
			{{ $column->column_code }}
			</td>
		@endforeach

		</tr>
		</thead>

		<!-- Table content with row information -->
		<tbody>
		@foreach( $template->rows as $row )

			<tr>
			<td style="background-color: #FAFAFA;">{{ $row->row_code }}</td>
			<td class="property{{ $row->row_property }}" style="background-color: #FAFAFA;">{{ $row->row_description }}</td>
			<!-- Table cell information, column and row combination -->
			@foreach( $template->columns as $column )

				<!-- Create a new variable, column and row combination -->
				{{--*/ $field = 'column' . $column->column_code . '-row' . $row->row_code /*--}}

				@if (array_key_exists($field, $disabledFields))
					<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="disabled" style="background-color: LightGray ! important;" id="{{ $field }}"></td>
				@else
					@if (strpos($field,$searchvalue) !== false)
						<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="tablecell highlight" id="{{ $field }}"></td>
					@else
						<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="tablecell" id="{{ $field }}"></td>
					@endif
				@endif

			@endforeach
			</tr>

		@endforeach
		</tbody>

		</table>

    @endif

	<p>
	{!! link_to_route('sections.index', 'Back to Sections') !!}
	</p>

	<!-- Modal pop-up -->
	<div class="modal fade" id="template-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="overflow:hidden;">
		<div class="modal-dialog" style="width: 90%;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title"><b>Field information dialog</b></h4>
				</div>
				<div class="modal-body" style="overflow-y: scroll; margin-right: 1px;">
					<div id="modalContent" style="display:none;">
					</div>
				</div>
				<div class="modal-footer">
					@if (!Auth::guest())
					<button type="button" id="modal-update" class="btn btn-warning">Change content</button>
					@endif
					<button type="button" id="modal-close" class="btn btn-default" data-dismiss="modal">Close</button>
				</div>
			</div><!-- /.modal-content -->
		</div><!-- /.modal-dialog -->
	</div><!-- /.modal -->

@endsection

@stop
