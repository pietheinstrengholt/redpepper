<!-- /resources/views/changerequests/edit.blade.php -->
@extends('layouts.master')

@section('content')
	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/changerequests'); !!}">Changerequests</a></li>
	  <li class="active">{{ $template->template_name }}</li>
	</ul>

	<h2>Review Changerequest</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif

	<!-- cell content -->
	@if ($changerequest->field_property1 !== '' || $changerequest->field_property2 !== '' || $changerequest->field_regulation !== '' || $changerequest->field_interpretation !== '')
		<table style="border: 1px solid #ddd;" class="table dialog table-striped">
		<tr>
			<td class="info-header"><h4><b>Specific Information: {{ $template_row->row_code }} - {{ $template_column->column_code }}</b></h4></td>
		</tr>
			<td class="changerequest">
			@if ($changerequest->field_property1 !== '')
				<dl class="dl-horizontal">
				<dt>{!! App\Helper::setting('fieldname_property1') !!}:</dt>
				<dd><div class="content-box">{!! $changerequest->field_property1 !!}</div></dd>
				</dl>
			@endif
			@if ($changerequest->field_property2 !== '')
				<dl class="dl-horizontal">
				<dt>{!! App\Helper::setting('fieldname_property2') !!}:</dt>
				<dd><div class="content-box">{!! $changerequest->field_property2 !!}</div></dd>
				</dl>
			@endif
			@if ($changerequest->field_interpretation !== '')
				<dl class="dl-horizontal">
				<dt>Interpretation:</dt>
				<dd><div class="content-box">{!! $changerequest->field_interpretation !!}</div></dd>
				</dl>
			@endif
			@if ($changerequest->field_regulation !== '')
				<dl class="dl-horizontal">
				<dt>Legal standard:</dt>
				<dd><div class="content-box">{!! $changerequest->field_regulation !!}</div></dd>
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

	<tr class="changerequest">
		<td class="info-left im-content">
			<h4><b>Name:</b></h4>
			<div rows="1" id="row_name">{{ $template_row->row_description }}</div>
			<h4><b>Interpretation:</b></h4>
			<div rows="6" id="row_interpretation">{!! nl2br($changerequest->interpretation_row) !!}</div>
			<h4><b>Legal standard:</b></h4>
			<div rows="7" id="row_regulation">{!! nl2br($changerequest->regulation_row) !!}</div>
		</td>
		<td class="info-right im-content">
			<h4><b>Name:</b></h4>
			<div rows="1" id="coloumn_name">{{ $template_column->column_description }}</div>
			<h4><b>Interpretation:</b></h4>
			<div rows="6" id="column_interpretation">{!! nl2br($changerequest->interpretation_column) !!}</div>
			<h4><b>Legal standard:</b></h4>
			<div rows="7" id="column_regulation">{!! nl2br($changerequest->regulation_column) !!}</div>
		</td>
	</tr>
	</table>

	@if ($changerequest->technical !== '')
		<table style="border: 1px solid #ddd;" class="table dialog table-striped">
		<tr>
			<td class="info-header">
				<h4><b>Additional Information for row {{ $template_row->row_code }} and column {{ $template_column->column_code }}:</b></h4>
			</td>
		</tr>
		<td>{!! nl2br($changerequest->technical) !!}</td>
		</table>
	@endif


	@if ($allowedToChange == 'yes' && $changerequest->status == 'pending' || (App\Helper::setting('superadmin_process_directly') == "yes" && Auth::user()->role == "superadmin"))
		{!! Form::open(array('action' => 'ChangeRequestController@update', 'id' => 'form')) !!}
		<textarea form="form" name="comment" style="width: 600px;" class="form-control" rows="3" id="comment" class="comment" placeholder="Please enter a comment about this change"></textarea>

		<div class="form-group">
		<button type="submit" id="approve" name="change_type" value="approved" class="changerequest btn btn-primary">Approve changerequest</button>
		<input type="hidden" name="_token" value="{!! csrf_token() !!}">
		<input type="hidden" name="changerequest_id" value="{!! $changerequest->id !!}">
		</div>

		<div class="form-group">
		<button type="submit" id="reject"  name="change_type" value="rejected" class="changerequest btn btn-danger">Reject changerequest</button>
		<input type="hidden" name="_token" value="{!! csrf_token() !!}">
		<input type="hidden" name="changerequest_id" value="{!! $changerequest->id !!}">
		</div>
		{!! Form::close() !!}
	@endif

	@if ($allowedToChange == 'yes' && $changerequest->status == 'rejected' || (App\Helper::setting('superadmin_process_directly') == "yes" && Auth::user()->role == "superadmin"))
		{!! Form::open(array('action' => 'ChangeRequestController@update', 'id' => 'form')) !!}
		<textarea form="form" name="comment" style="width: 600px;" class="form-control" rows="3" id="comment" class="comment" placeholder="Please enter a comment about this change"></textarea>

		<div class="form-group">
		<button type="submit" id="reopen" name="change_type" value="reopen" class="changerequest btn btn-warning">Reopen changerequest</button>
		<input type="hidden" name="_token" value="{!! csrf_token() !!}">
		<input type="hidden" name="changerequest_id" value="{!! $changerequest->id !!}">
		</div>
		{!! Form::close() !!}
	@endif

@endsection
