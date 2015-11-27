<!-- /resources/views/changerequests/index.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Changerequests</h2>
	<h4>Please make a selection of one of the following types</h4>
 
    @if ( !$changerequests->count() )
        No types found in the database!
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
		<tr>
			<td>{{ $changerequest->id }}</td>
			<td>{{ $changerequest->template->template_name }}</td>
			<td>{{ $changerequest->creator_id }}</td>
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
		@endforeach

		</table>
    @endif
	
@endsection

@stop