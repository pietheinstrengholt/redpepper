<!-- /resources/views/fileupload/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Files</li>
	</ul>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	<h2>Uploaded files and instruction manuals</h2>
	<h4>Please make a selection of one of the following files</h4>

	@if ( !$files->count() )
		No uploaded files found in the database!<br><br>
		@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header" style="width:30%;">File name</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $files as $file )
			<tr>
			<td><a href="{{ URL::asset('/files') }}/{{ $file->file_name }}">{{ $file->file_name }}</a></td>
			<td>{{ $file->file_description }}</td>
			@can('superadmin')
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('fileupload.destroy', $file->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this file?\')')) !!}
				<td>
				{!! link_to_route('fileupload.edit', 'Edit', array($file->id), array('class' => 'btn btn-info btn-xs')) !!}
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
				</td>
				{!! Form::close() !!}
			@else
				<td></td>
			@endcan
			</tr>
		@endforeach

		</table>
	@endif

	@can('superadmin')
		<p>
		{!! link_to_route('fileupload.create', 'Upload new file') !!}
		</p>
	@endcan

@endsection
