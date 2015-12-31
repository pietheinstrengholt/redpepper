<!-- /resources/views/template/show.blade.php -->
@extends('layouts.master')

@section('content')

	<h2>{{ $template->template_name }}</h2>
	<h4>{{ $template->template_shortdesc }}</h4>
	<h4>{{ $template->template_longdesc }}</h4>

	@if ( !$template->columns->count() || !$template->rows->count() )
		Error: This template has no columns or no rows.
	@else

		{!! Form::open(array('action' => 'TemplateController@changestructure', 'id' => 'form')) !!}
		<input name="template_id" type="hidden" value="{{ $template->id }}"/>
		<input name="section_id" type="hidden" value="{{ $template->section_id }}"/>
		<button style="margin-bottom:15px;" type="submit" class="btn btn-warning">Submit new template structure</button>

		<table class="table table-bordered template template-structure" border="1">

		<!-- Table header with column names -->
		<thead>
		<tr class="success">

		<td class="header structure content">Row#</td>
		<td class="header structure">Row description</td>
		<td class="header structure">Styling</td>

		@foreach( $template->columns as $column )
			<td style="width: 150px;" class="content header" id="$column->column_num">
				<textarea class="form-control input-sm" rows="6" name="coldesc[{{ $column->column_code }}]" placeholder="{{ $column->column_description }}">{{ $column->column_description }}</textarea>
			</td>
		@endforeach
		</tr>

		<!-- Table header with column nums -->

		<tr class="header2">

		<td></td>
		<td></td>
		<td></td>

		@foreach( $template->columns as $column )
			<td style="text-align: center; font-weight: bold;">
				<input class="form-control input-sm" type="text" value="{{ $column->column_code }}" name="colnum[{{ $column->column_code }}]" placeholder="{{ $column->column_code }}" style="width: 60px;">
			</td>
		@endforeach

		</tr>
		</thead>

		<!-- Table content with row information -->
		<tbody>
		@foreach( $template->rows as $row )

			<tr>
			<td class="desc"><input class="form-control input-sm" type="text" value="{{ $row->row_code }}" placeholder="{{ $row->row_code }}" name="rownum[{{ $row->row_code }}]" style="width: 50px;"></td>
			<td class="desc"><input class="form-control input-sm" type="text" placeholder="{{ $row->row_description }}" value="{{ $row->row_description }}" name="rowdesc[{{ $row->row_code }}]"></td>
			<td class="desc">
				<label class="checkbox-inline">
				@if ($row['row_property'] == "bold")
					<input class="rowproperty{{ $row->row_code }}" type="radio" name="row_property[{{ $row->row_code }}]" id="radio{{ $row->row_code }}" value="bold" checked> bold
				@else
					<input class="rowproperty{{ $row->row_code }}" type="radio" name="row_property[{{ $row->row_code }}]" id="radio{{ $row->row_code }}" value="bold"> bold
				@endif
				</label>

				<label class="checkbox-inline">
				@if ($row['row_property'] == "tab")
					<input class="rowproperty{{ $row->row_code }}" type="radio" name="row_property[{{ $row->row_code }}]" id="radio{{ $row->row_code }}" value="tab" checked> tab
				@else
					<input class="rowproperty{{ $row->row_code }}" type="radio" name="row_property[{{ $row->row_code }}]" id="radio{{ $row->row_code }}" value="tab"> tab
				@endif
				</label>
				<label class="checkbox-inline">
				@if ($row['row_property'] == "doubletab")
					<input class="rowproperty{{ $row->row_code }}" type="radio" name="row_property[{{ $row->row_code }}]" id="radio{{ $row->row_code }}" value="doubletab" checked> doubletab
				@else
					<input class="rowproperty{{ $row->row_code }}" type="radio" name="row_property[{{ $row->row_code }}]" id="radio{{ $row->row_code }}" value="doubletab"> doubletab
				@endif
				</label>
			</td>

			<!-- Table cell information, column and row combination -->
			@foreach( $template->columns as $column )

				<!-- Create a new variable, column and row combination -->
				{{--*/ $field = 'column' . $column->column_code . '-row' . $row->row_code /*--}}

				@if (array_key_exists($field, $disabledFields))
					<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="value" class="disabled" id="{{ $field }}"><input style="display:none;" checked="checked" type="checkbox" name="options[]" value="{{ $field }}" /></td>
				@else
					<td title="{{ $column->column_description }} - {{ $row->row_description }}" class="value" id="{{ $field }}"><input style="display:none;" type="checkbox" name="options[]" value="{{ $field }}" /></td>
				@endif

			@endforeach
			</tr>

		@endforeach
		</tbody>

		</table>

	@endif

	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}

	<p>
	{!! link_to_route('sections.index', 'Back to Sections') !!}
	</p>

@endsection