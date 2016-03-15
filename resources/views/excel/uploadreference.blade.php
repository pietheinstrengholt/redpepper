<!-- /resources/views/template/uploadreference.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Upload type descriptions</li>
	</ul>

	<h2>Upload type descriptions</h2>
	<h4>Upload an excel file with on the first column 'value', 'description' and with sheetname 'content'</h4>
	<img style="margin-bottom: 5px; margin-left: 3px;" src="{{ URL::asset('img/example_reference_table.jpg') }}">

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'ExcelController@uploadreferenceexcel', 'id' => 'form', 'files'=> 'true')) !!}

	<br>
	{!! Form::file('excel') !!}
	<p class="errors">{!!$errors->first('excel')!!}</p>

	<div class="form-group">
	<label for="caption">Type name</label>
	{!! Form::select('type_id', $types->lists('type_name', 'id'), null, ['id' => 'type_id', 'class' => 'form-control']) !!}
	</div>

	<button type="submit" class="btn btn-primary">Upload</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}

@endsection
