<!-- /resources/views/changerequests/edit.blade.php -->
@extends('layouts.master')

@section('content')

<head>
<style type="text/css">
ins {
	color: green;
	background: #dfd;
	text-decoration: none;
	}
del {
	color: red;
	background: #fdd;
	text-decoration: none;
	}
code {
	font-size: smaller;
	}
#params {
	margin: 1em 0;
	font: 14px sans-serif;
	}
.code {
	margin-left: 2em;
	font: 12px monospace;
	}
.ins {
	background:#dfd;
	}
.del {
	background:#fdd;
	}
.rep {
	color: #008;
	background: #eef;
	}
.panecontainer {
	display: inline-block;
	width: 49.5%;
	vertical-align: top;
	}
.panecontainer > p {
	margin: 0;
	border: 1px solid #bcd;
	border-bottom: none;
	padding: 1px 3px;
	background: #def;
	font: 14px sans-serif
	}
.panecontainer > p + div {
	margin: 0;
	padding: 2px 0 2px 2px;
	border: 1px solid #bcd;
	border-top: none;
	}
.pane {
	margin: 0;
	padding: 0;
	border: 0;
	width: 100%;
	min-height: 20em;
	overflow:auto;
	font: 12px monospace;
	}
#htmldiff.onlyDeletions ins {display:none}
#htmldiff.onlyInsertions del {display:none}
</style>
</head>

<h2>Review Changerequest</h2>

<!-- cell content -->
@if ($changerequest->field_property1 !== '' || $changerequest->field_property2 !== '' || $changerequest->field_regulation !== '' || $changerequest->field_interpretation !== '')
	<table style="border: 1px solid #ddd;" class="table dialog table-striped">
	<tr>
	<td class="info-header"><h4><b>Specific Information: {{ $template_row->row_code }} - {{ $template_column->column_code }}</b></h4></td>
	</tr>
	<td>
	@if ($changerequest->field_property1 !== '')
		<dl class="dl-horizontal" style="margin-right: 30px;">
		<dt>Fieldname property1:</dt>
		<dd><div class="content-box" style="float:left;">{!! $changerequest->field_property1 !!}</div></dd>
		</dl>
	@endif
	@if ($changerequest->field_property2 !== '')
		<dl class="dl-horizontal" style="margin-right: 30px;">
		<dt>Fieldname property2:</dt>
		<dd><div class="content-box" style="float:left;">{!! $changerequest->field_property2 !!}</div></dd>
		</dl>
	@endif
	@if ($changerequest->field_regulation !== '')
		<dl class="dl-horizontal" style="margin-right: 30px;">
		<dt>Legal standard:</dt>
		<dd><div class="content-box" style="float:left;">{!! $changerequest->field_regulation !!}</div></dd>
		</dl>
	@endif
	@if ($changerequest->field_interpretation !== '')
		<dl class="dl-horizontal" style="margin-right: 30px;">
		<dt>Interpretation:</dt>
		<dd><div class="content-box" style="float:left;">{!! $changerequest->field_interpretation !!}</div></dd>
		</dl>
	@endif
	</td>
	</table>
@endif


<!-- legal and interpretations content -->
<table style="border: 1px solid #ddd;" border="0" class="table dialog table-striped">
<tr>
	<td class="info-header"><h4><b>Row information: {{ $template_row->row_code }}</b></h4></td>
	<td class="info-header"><h4><b>Column information: {{ $template_column->column_code }}</b></h4></td>
</tr>

<tr>
	<td class="info-left im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" id="rowname">{{ $template_row->row_description }}</div>
		<h4><b>Legal standard:</b></h4>
		<div rows="7" id="row_legal">{!! nl2br($changerequest->regulation_row) !!}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" id="row_interpretation">{!! nl2br($changerequest->interpretation_row) !!}</div>
	</td>
	<td class="info-right im-content">
		<h4><b>Name:</b></h4>
		<div rows="1" id="colname">{{ $template_column->column_description }}</div>
		<h4><b>Legal standard:</b></h4>
		<div rows="7" id="column_legal">{!! nl2br($changerequest->regulation_column) !!}</div>
		<h4><b>Interpretation:</b></h4>
		<div rows="6" id="row_interpretation">{!! nl2br($changerequest->interpretation_column) !!}</div>
	</td>
</tr>
</table>

@if ($changerequest->technical !== '')
	<table style="border: 1px solid #ddd;" class="table dialog table-striped">
	<tr><td class="info-header"><h4><b>Additional Information for row {{ $template_row->row_code }} and column {{ $template_column->column_code }}:</b></h4></td></tr>
	<td>{!! nl2br($changerequest->technical) !!}</td>
	</table>
@endif


@if ($allowedToChange == 'yes' && $changerequest->status == 'pending')
	<textarea form="form" name="comment" style="width: 600px;" class="form-control" rows="3" id="comment" class="comment" placeholder="Please enter a comment about this change"></textarea>

	{!! Form::open(array('action' => 'ChangeRequestController@update', 'id' => 'form')) !!}
	<div class="form-group">
	<button type="submit" class="changerequest btn btn-primary">Approve changerequest</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">	
	<input type="hidden" name="changerequest_id" value="{!! $changerequest->id !!}">	
	<input type="hidden" name="change_type" value="approved">	
	</div>
	{!! Form::close() !!}

	{!! Form::open(array('action' => 'ChangeRequestController@update', 'id' => 'form')) !!}
	<div class="form-group">
	<button type="submit" class="changerequest btn btn-danger">Reject changerequest</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	<input type="hidden" name="changerequest_id" value="{!! $changerequest->id !!}">	
	<input type="hidden" name="change_type" value="rejected">
	</div>
	{!! Form::close() !!}
@endif

@endsection

@stop