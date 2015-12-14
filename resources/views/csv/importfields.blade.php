<!-- /resources/views/csv/importfields.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li class="active">Upload content</li>
	</ul>

	<h2>Upload field line items</h2>
	<h4>Please make use of the upload form below</h4>

	<strong>Example:</strong> The file needs to be in the following format, including header and stored as comma separated<br><br>
	<pre>template_id;row_code;column_code;property;content
	5;1;010;010;legal_desc;100% minus the discount
	5;2;010;020;interpretation_desc;lowest percentage
	5;3;010;030;interpretation_desc;only to central bank
	5;4;010;040;legal_desc;lowest percentage</pre>

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
	<input type="hidden" name="formname" value="importfields">
	{!! Form::close() !!}

@endsection

@stop
