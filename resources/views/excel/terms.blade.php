@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Import terms</li>
	</ul>

	<h2>Import terms and relations into a Glossary</h2>
	<h4>Please make use of the upload form below</h4>

	<p>This page can be used to import terms into the application. The Excel file to be used contains a single sheet. The terms sheet is used for the terms and definitions. Be sure that every term uses its own unique id and the sequence is correct.</p>
	<p><a href="{{ url('/excel/termsdownload') }}">Download the excel template</a></p>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'ExcelController@postterms', 'id' => 'form', 'files'=> 'true')) !!}

	<br>
	{!! Form::file('excel') !!}
	<p class="errors">{!! $errors->first('excel') !!}</p>

	<button type="submit" class="btn btn-primary">Upload</button>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}

@endsection
