<!-- /resources/views/template/upload.blade.php -->
@extends('layouts.master')

@section('content')

<h2>Upload a new template</h2>
<h4>Please make use of the upload form below</h4>

{!! Form::open(array('action' => 'ExcelController@uploadexcel', 'id' => 'form', 'files'=> 'true')) !!}

<br>
{!! Form::file('excel') !!}
<p class="errors">{!!$errors->first('excel')!!}</p>

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

@stop