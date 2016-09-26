<!-- /resources/views/fileupload/create.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/fileupload'); !!}">Files</a></li>
	  <li class="active">Upload new file</li>
	</ul>

	<h2>Upload new file</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'FileUploadController@store', 'id' => 'form', 'files'=> 'true')) !!}

	<div class="form-horizontal">

		<div class="form-group">
			{!! Form::label('file_description', 'File description:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-6">
			{!! Form::textarea('file_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
			</div>
		</div>

		<div class="form-group">
			<div class="col-sm-3">
			</div>
			<div class="col-sm-6">
			{!! Form::file('fileupload') !!}
			<p class="errors">{!! $errors->first('fileupload') !!}</p>
			</div>
		</div>

		<div class="form-group">
			<button type="submit" class="btn btn-primary">Upload</button>
		</div>

	</div>

	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}

@endsection
