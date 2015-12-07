<!-- /resources/views/changerequests/index.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Changerequests</h2>
	<h4>Please make a selection of one of the following types</h4>
 
    @if ( !$changerequests->count() )
        No types found in the database!<br><br>
    @else
		<table class="section-table table table-condensed sortable" border="1">
		
		<tr class="success" style="background-color: #dff0d8;">
			<td><b>ID</b></td>
			<td style="width: 300px;"><b>Template</b></td>
			<td><b>Username</b></td>
			<td><b>Name</b></td>
			<td><b>Report</b></td>
			<td style="width: 100px;"><b>Submission date</b></td>
			<td style="width: 100px;"><b>Modified</b></td>
			<td><b>Status</b></td>
			<td style="width: 300px;"><b>Comment</b></td>
			<td class="header" style="width: 140px;">Options</td>			
		</tr>
		
		@foreach( $changerequests as $changerequest )
		
		@if ( $changerequest->status == "pending" )
			<tr>
				<td>{{ $changerequest->id }}</td>
				<td><a href="<?php echo url('sections/' . $changerequest->template->section_id . '/templates/' . $changerequest->template_id . '?row=' . $changerequest->row_number . '&column=' . $changerequest->column_number); ?>">{{ $changerequest->template->template_name }}</a></td>
				<td>{{ $changerequest->creator->username }}</td>
				<td></td>
				<td>{{ $changerequest->template->section->section_name }}</td>
				<td>{{ $changerequest->created_at }}</td>
				<td>{{ $changerequest->updated_at }}</td>
				<td>{{ $changerequest->status }}</td>
				<td>{{ $changerequest->comment }}</td>
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('changerequests.destroy', $changerequest->id))) !!}
				<td>
					{!! link_to_route('changerequests.edit', 'Review', array($changerequest->id), array('class' => 'btn btn-info btn-xs')) !!}
					{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
				</td>
				{!! Form::close() !!}
			</tr>
		@else
			<tr style="background-color:#F5F5F5;">
				<td>{{ $changerequest->id }}</td>
				<td><a href="<?php echo url('sections/' . $changerequest->template->section_id . '/templates/' . $changerequest->template_id . '?row=' . $changerequest->row_number . '&column=' . $changerequest->column_number); ?>">{{ $changerequest->template->template_name }}</a></td>
				<td>{{ $changerequest->creator->username }}</td>
				<td></td>
				<td>{{ $changerequest->template->section->section_name }}</td>
				<td>{{ $changerequest->created_at }}</td>
				<td>{{ $changerequest->updated_at }}</td>
				<td>{{ $changerequest->status }}</td>
				<td>{{ $changerequest->comment }}</td>
				<td></td>
			</tr>
		@endif
		
		@endforeach

		</table>
    @endif
	
	{!! Form::open(array('action' => 'ChangeRequestController@cleanup', 'id' => 'form')) !!}
	<button type="submit" class="btn btn-primary changerequest">Cleanup changerequests</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}
	
	{!! Form::open(array('action' => 'ExcelController@exportchanges', 'id' => 'form')) !!}
	<button type="submit" class="btn btn-warning changerequest">Export changerequests</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}		
	
@endsection

@stop