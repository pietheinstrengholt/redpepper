<!-- /resources/views/csv/importrows.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Upload content</li>
	</ul>

	<h2>Upload template rows</h2>
	<h4>Please make use of the upload form below</h4>

	<strong>Example:</strong> The file needs to be in the following format, including header and stored as comma separated<br><br>
	<pre>template_id;row_num;row_code;row_description
	5;1;010;Row description 010
	5;2;020;Row description 020
	5;3;030;Row description 030
	5;4;040;Row description 040</pre>

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
	<input type="hidden" name="formname" value="importrows">
	{!! Form::close() !!}

@endsection