<!-- /resources/views/template/cell.blade.php -->

<!-- cell content -->
<table class="table dialog table-striped">
<tr>
<td class="info-header"><h4><b>Specific Information: {{ $row->row_code }} - {{ $column->column_code }}</b></h4></td>
</tr>
<td>
@if ( $field_property1 )
	<dl class="dl-horizontal">
	<dt>{!! App\Helper::setting('fieldname_property1') !!}:</dt>
	<dd><div title="{!! App\Helper::returnHistory($field_property1) !!}" class="content-box">{{ $field_property1->content }}</div></dd>
	</dl>
@endif
@if ( $field_property2 )
	<dl class="dl-horizontal">
	<dt>{!! App\Helper::setting('fieldname_property2') !!}:</dt>
	<dd><div title="{!! App\Helper::returnHistory($field_property2) !!}" class="content-box">{{ $field_property2->content }}</div></dd>
	</dl>
@endif
@if ( $field_regulation )
	<dl class="dl-horizontal">
	<dt>Regulation:</dt>
	<dd><div title="{!! App\Helper::returnHistory($field_regulation) !!}" class="content-box">{!! App\Helper::contentAdjust($field_regulation->content) !!}</div></dd>
	</dl>
@endif
@if ( $field_interpretation )
	<dl class="dl-horizontal">
	<dt>Interpretation:</dt>
	<dd><div title="{!! App\Helper::returnHistory($field_interpretation) !!}" class="content-box">{!! App\Helper::contentAdjust($field_interpretation->content) !!}</div></dd>
	</dl>
@endif
</td>
</table>

<!-- legal and interpretations content -->
<table class="table dialog table-striped">
<tr>
	<td class="info-header"><h4><b>Row information: {{ $row->row_code }}</b></h4></td>
	<td class="info-header"><h4><b>Column information: {{ $column->column_code }}</b></h4></td>
</tr>

<tr>
	<td class="info-left im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" title="{!! App\Helper::returnHistory($row) !!}" id="rowname">{{ $row->row_description }}</div>
		<h4><b>Regulation:</b></h4>
		<div rows="7" title="{!! App\Helper::returnHistory($regulation_row) !!}" id="row_regulation">{!! App\Helper::contentAdjust(nl2br(e($regulation_row['content']))) !!}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" title="{!! App\Helper::returnHistory($interpretation_row) !!}" id="row_interpretation">{!! App\Helper::contentAdjust(nl2br(e($interpretation_row['content']))) !!}</div>
	</td>
	<td class="info-right im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" title="{!! App\Helper::returnHistory($column) !!}" id="colname">{{ $column->column_description }}</div>
		<h4><b>Regulation:</b></h4>
		<div rows="7" title="{!! App\Helper::returnHistory($regulation_column) !!}" id="column_regulation">{!! App\Helper::contentAdjust(nl2br(e($regulation_column['content']))) !!}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" title="{!! App\Helper::returnHistory($interpretation_column) !!}" id="column_interpretation">{!! App\Helper::contentAdjust(nl2br(e($interpretation_column['content']))) !!}</div>
	</td>
</tr>
</table>

<!-- technical content -->
@if ( $technical->count() )

	<!-- technical content -->
	<table class="table dialog table-striped">
		<tr>
			<td class="info-header"><h4><b>Technical information</b></h4></td>
		</tr>
		<td>
			<table class="table" id="technical" border="1">
				<tr class="success">
					<th class="source">System</th>
					<th class="type">Type</th>
					<th class="content">Value</th>
					<th class="description">Description</th>
				</tr>

				@foreach( $technical as $row )
					@if (is_object($row->source) && is_object($row->type))
						<tr id="{{ $row->id }}">
							<td class="source">{{ $row->source->source_name }}</td>
							<td class="type">{{ $row->type->type_name }}</td>
							<td class="content">{{ $row->content }}</td>

							@if ( $row->type->descriptions->count() )
								@foreach( $row->type->descriptions as $description )
									@if ($description->content == $row->content)
										{{--*/ $description_type = $description->description; /*--}}
									@endif
								@endforeach						
							@else
								{{--*/ $description_type = $row->description; /*--}}
							@endif

							@if ( empty($description_type) )
								{{--*/ $description_type = $row->description; /*--}}
							@endif							

							<td class="description">{{ $description_type }}</td>
						</tr>
					@endif
				@endforeach

			</table>
		</td>
	</table>

@endif
