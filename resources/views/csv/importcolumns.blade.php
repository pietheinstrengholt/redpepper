<!-- /resources/views/csv/importcolumns.blade.php -->
@extends('layouts.master')

@section('content')

<h2>Upload template columns</h2>
<h4>Please make use of the upload form below</h4>

<strong>Example:</strong> The file needs to be in the following format, including header and stored as comma separated<br><br>
<pre>template_id;column_num;column_name;column_description
5;1;010;Column description 010
5;2;020;Column description 020
5;3;030;Column description 030
5;4;040;Column description 040</pre>

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
<input type="hidden" name="formname" value="importcolumns">
{!! Form::close() !!}
	
@endsection

@stop