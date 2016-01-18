<!-- /resources/views/csv/importfields.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Upload content</li>
	</ul>

	<h2>Upload row, column or cell content</h2>
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

	<strong>Example:</strong> The file needs to be in the following format, including header and stored as comma separated<br><br>
	<pre>template_id;row_code;column_code;content_type;content
5;010;010;regulation;100% minus the discount
5;010;020;interpretation_desc;lowest percentage
5;010;030;interpretation_desc;only to central bank
5;010;040;regulation;lowest percentage</pre>

	{!! Form::open(array('action' => 'CSVController@uploadcsv', 'id' => 'form', 'files'=> 'true')) !!}

	<br>
	{!! Form::file('csv') !!}
	<p class="errors">{!!$errors->first('csv')!!}</p>

	<div class="form-group">
	<label for="caption">Template name</label>
	{!! Form::select('template_id', $templates->lists('template_name', 'id'), null, ['id' => 'template_id', 'class' => 'form-control']) !!}
	</div>

	<button type="submit" class="btn btn-primary">Upload</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	<input type="hidden" name="formname" value="importcontent">
	{!! Form::close() !!}

@endsection
