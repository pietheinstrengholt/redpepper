<!-- /resources/views/csv/importcontent.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li class="active">Upload content</li>
	</ul>

	<h2>Upload content</h2>
	<h4>Please make use of the upload form below</h4>

	<strong>Example:</strong> The file needs to be in the following format, including header and stored as comma separated<br><br>
	<pre>template_id;field_id;content_type;content
	5;C-010;reference;IFRS 5.33(b)(i)
	5;R-010;regulation;;This row is a residual category...
	5;R-020;interpretation;Internal policy follows..</pre>

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
	<input type="hidden" name="formname" value="importrequirements">
	{!! Form::close() !!}

@endsection

@stop
