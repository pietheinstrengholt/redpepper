<!-- /resources/views/template/cell.blade.php -->

<!-- cell content -->
<table style="border: 1px solid #ddd;" class="table dialog table-striped">
<tr>
<td class="info-header"><h4><b>Specific Information: {{ $row->row_name }} - {{ $column->column_name }}</b></h4></td>
</tr>
<td>
@if ( $field_property1->count() )
	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Fieldname property1:</dt>
	<dd><div class="content-box" style="float:left;">{{ $field_property1{0}->content }}</div></dd>
	</dl>
@endif
@if ( $field_property2->count() )
	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Fieldname property2:</dt>
	<dd><div class="content-box" style="float:left;">{{ $field_property2{0}->content }}</div></dd>
	</dl>
@endif
@if ( $field_regulation->count() )
	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Regulation:</dt>
	<dd><div class="content-box" style="float:left;">{{ $field_regulation{0}->content }}</div></dd>
	</dl>
@endif
@if ( $field_interpretation->count() )
	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Interpretation:</dt>
	<dd><div class="content-box" style="float:left;">{{ $field_interpretation{0}->content }}</div></dd>
	</dl>
@endif
</td>
</table>

<!-- legal and interpretations content -->
<table style="border: 1px solid #ddd;" border="0" class="table dialog table-striped">
<tr>
	<td class="info-header"><h4><b>Row information: {{ $row->row_name }}</b></h4></td>
	<td class="info-header"><h4><b>Column information: {{ $column->column_name }}</b></h4></td>
</tr>

<tr>
	<td class="info-left im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" id="rowname">{{ $row->row_description }}</div>
		<h4><b>Regulation:</b></h4>
		<div rows="7" id="row_regulation">{{ $regulation_row['content'] }}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" id="row_interpretation">{{ $interpretation_row['content'] }}</div>
	</td>
	<td class="info-right im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" id="colname">{{ $column->column_description }}</div>
		<h4><b>Regulation:</b></h4>
		<div rows="7" id="column_regulation">{{ $regulation_column['content'] }}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" id="column_interpretation">{{ $interpretation_column['content'] }}</div>
	</td>
</tr>
</table>

<!-- technical content -->
@if ( $technical->count() )

	<table border="0" class="table technical table-striped">
	<td style="background-color:#fff ! important;">
	<h4><b>Additional information:</b></h4>
	<p style="width: 100%;">
	
	<table class="table" id="technical" border="1">
	<tr class="success">
	<th>System</th>
	<th>Type</th>
	<th>Value</th>
	<th>Description</th>
	</tr>

	@foreach( $technical as $row )
		<tr id="{{ $row->id }}">
		<td>{{ $row->source->source_name }}</td>
		<td>{{ $row->type->type_name }}</td>
		<td>{{ $row->content }}</td>
		<td>{{ $row->description }}</td>
		</tr>
	@endforeach
	</td>
	</hr>
	</p>
	</table>

@endif