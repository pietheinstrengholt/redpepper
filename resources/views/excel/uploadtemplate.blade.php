<!-- /resources/views/template/uploadtemplate.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Upload excel template</li>
	</ul>

	<h2>Upload a new template</h2>
	<h4>Please make use of the upload form below</h4>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'ExcelController@uploadtemplateexcel', 'id' => 'form', 'files'=> 'true')) !!}

	<br>
	{!! Form::file('excel') !!}
	<p class="errors">{!! $errors->first('excel') !!}</p>

	<div class="form-group">
	<label for="caption">Section name</label>
	{!! Form::select('section_id', $sections->lists('section_name', 'id'), null, ['id' => 'section_id', 'class' => 'form-control']) !!}
	</div>

	<div class="form-group">
	<label for="caption">Template name</label>
	<input type="text" class="form-control" name="template_name" value="">
	</div>

	<div class="form-group">
	<label for="description">Description</label>
	<textarea class="form-control" name="template_description"></textarea>
	</div>

	<button type="submit" class="btn btn-primary">Upload</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}

@endsection
