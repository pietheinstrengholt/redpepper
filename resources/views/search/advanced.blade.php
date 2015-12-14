<!-- /resources/views/search/advanced.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>Advanced search</h2>
	<h4>Use the search form below</h4>

	{!! Form::open(array('action' => 'SearchController@search', 'id' => 'form')) !!}

	<div class="panel panel-default">
	<div class="panel-heading">Search</div>
	<div class="panel-body">

	<div class="form-group">
	<label for="caption">Search for:</label>
	<input type="text" class="form-control" name="search" value="">
	</div>

	<div class="row">
	<div class="col-md-1">
	</div>
	<div class="col-md-4 advanced-search">
	<h4>Sections</h4>
	@if ( $sections->count() )
		@foreach( $sections as $section )
			<div class="checkbox">
			<label><input type="checkbox" name="sections[{{ $section->id }}]" value="{{ $section->id }}">{{ $section->section_name }}</label>
			</div>
		@endforeach
	@endif
	</div>


	<div class="col-md-4 advanced-search">
	<h4>Content types</h4>

	<div class="checkbox"><label><input type="checkbox" name="types[1]" value="regulation">Regulation</label></div>
	<div class="checkbox"><label><input type="checkbox" name="types[2]" value="interpretation">Interpretations</label></div>
	<div class="checkbox"><label><input type="checkbox" name="types[3]" value="property1">Reporting Business Rule</label></div>
	<div class="checkbox"><label><input type="checkbox" name="types[4]" value="property2">Standard Operating Procedure</label></div>

	</div>
	</div>
	</div>
	<div class="col-md-1">
	</div>
	<button type="submit" class="btn btn-primary">Search</button>

	</div>

	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}

@endsection

@stop
