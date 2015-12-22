<!-- /resources/views/csv/importtech.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Upload content</li>
	</ul>

	<h2>Upload technical line items</h2>
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
	<pre>template_id;source_id;type_id;content;row_code;column_code;description
	999;5;1;10001010;020;010;Cash on hand
	999;5;1;10002010;030;010;Balances with central banks other than mandatory reserve deposits and readily convertible in cash
	999;5;1;10004011;040;010;Due from credit institutions - Current accounts (nostro accounts)
	999;5;1;11007010;040;010;Due from credit institutions - Overdrafts current accounts</pre>

	{!! Form::open(array('action' => 'CSVController@uploadcsv', 'id' => 'form', 'files'=> 'true')) !!}

	<br>
	{!! Form::file('csv') !!}
	<p class="errors">{!!$errors->first('csv')!!}</p>

	<div class="form-group">
	<label for="caption">Section name</label>
	{!! Form::select('section_id', $sections->lists('section_name', 'id'), null, ['id' => 'section_id', 'class' => 'form-control']) !!}
	</div>

	<button type="submit" class="btn btn-primary">Upload</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	<input type="hidden" name="formname" value="importtech">
	{!! Form::close() !!}

@endsection