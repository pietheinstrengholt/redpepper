<!-- /resources/views/template/cell.blade.php -->

<!-- cell content -->
<table class="table dialog table-striped">
<tr>
<td class="info-header"><h4><b>Specific Information: {{ $row->row_name }} - {{ $column->column_name }}</b></h4></td>
</tr>
<td>
@if ( $field_property1->count() )
	<dl class="dl-horizontal">
	<dt>Fieldname property1:</dt>
	<dd><div class="content-box">{{ $field_property1{0}->content }}</div></dd>
	</dl>
@endif
@if ( $field_property2->count() )
	<dl class="dl-horizontal">
	<dt>Fieldname property2:</dt>
	<dd><div class="content-box">{{ $field_property2{0}->content }}</div></dd>
	</dl>
@endif
@if ( $field_regulation->count() )
	<dl class="dl-horizontal">
	<dt>Regulation:</dt>
	<dd><div class="content-box">{!! $field_regulation{0}->content !!}</div></dd>
	</dl>
@endif
@if ( $field_interpretation->count() )
	<dl class="dl-horizontal">
	<dt>Interpretation:</dt>
	<dd><div class="content-box">{!! $field_interpretation{0}->content !!}</div></dd>
	</dl>
@endif
</td>
</table>

<!-- legal and interpretations content -->
<table class="table dialog table-striped">
<tr>
	<td class="info-header"><h4><b>Row information: {{ $row->row_name }}</b></h4></td>
	<td class="info-header"><h4><b>Column information: {{ $column->column_name }}</b></h4></td>
</tr>

<tr>
	<td class="info-left im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" id="rowname">{{ $row->row_description }}</div>
		<h4><b>Regulation:</b></h4>
		<div rows="7" id="row_regulation">{!! nl2br(e($regulation_row['content'])) !!}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" id="row_interpretation">{!! nl2br(e($interpretation_row['content'])) !!}</div>
	</td>
	<td class="info-right im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" id="colname">{{ $column->column_description }}</div>
		<h4><b>Regulation:</b></h4>
		<div rows="7" id="column_regulation">{!! nl2br(e($regulation_column['content'])) !!}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" id="column_interpretation">{!! nl2br(e($interpretation_column['content'])) !!}</div>
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
					<tr id="{{ $row->id }}">
						<td class="source">{{ $row->source->source_name }}</td>
						<td class="type">{{ $row->type->type_name }}</td>
						<td class="content">{{ $row->content }}</td>
						<td class="description">{{ $row->description }}</td>
					</tr>
				@endforeach
				
			</table>
		</td>
	</table>			

@endif