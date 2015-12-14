<!-- /resources/views/manuals/show.blade.php -->
@extends('layouts.master')

@section('content')
  <h2>{{ $section->section_name }}</h2>
	<h4>Total overview of all templates</h4>

  @if ( !$section->templates->count() )
      This section has no templates.<br><br>
  @else
  	@foreach( $templates as $template )
  		<h4>{{ $template->template_name }}</h4>
  		<h5>{{ $template->template_shortdesc }}</h5>
  		<h5>{{ $template->section_shortdesc }}</h5>
  		<h5>{{ $template->template_longdesc }}</h5>

  		@if ( $template->requirements->count() )
  			<table class="table table-bordered book" border="0">
  			<tr class="success">
  			<td>Fieldname</td>
  			<td>Content type</td>
  			<td>Requirement</td>
  			</tr>
  			@foreach( $template->requirements as $requirement )
  				<tr>
  				<td>{{ $requirement->field_id }}</td>
  				<td>{{ $requirement->content_type }}</td>
  				<td>{{ $requirement->content }}</td>
  				</tr>
  			@endforeach
  			</table>
  		@endif

  	@endforeach
  @endif

  <p><a href="{{ url('manuals') }}">Back to manuals</a></p>

@endsection

@stop
