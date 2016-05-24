<!-- /resources/views/manuals/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li><a href="{!! url('/manuals'); !!}">Manuals</a></li>
	  <li class="active">{{ $section->section_name }}</li>
	</ul>

	<h2>{{ $section->section_name }}</h2>
	<h4>{{ $section->section_description }}</h4>
	<h4>{!! html_entity_decode(e($section->section_longdesc)) !!}</h4><br>
	<h3>Total overview of all templates</h3>

	@if ( !$section->templates->count() )
		This section has no templates.<br><br>
	@else
		@foreach( $templates as $template )
			<h4>{{ $template->template_name }}</h4>
			<h5>{{ $template->template_shortdesc }}</h5>
			<h5>{{ $template->section_shortdesc }}</h5>
			<h5>{!! html_entity_decode(e($template->template_longdesc)) !!}</h5>

			@if ( $template->requirements->count() )
				<table class="table table-bordered book" border="0">
				<tr class="success">
				<td>Row code</td>
				<td>Column code</td>
				<td>Content type</td>
				<td>Requirement</td>
				</tr>
				@foreach( $template->requirements as $requirement )
					@if ( $requirement->content_type != "disabled" )
						<tr>
						<td>{{ $requirement->row_code }}</td>
						<td>{{ $requirement->column_code }}</td>
						<td>{{ $requirement->content_type }}</td>
						<td>{{ $requirement->content }}</td>
						</tr>
					@endif
				@endforeach
				</table>
			@endif
		@endforeach
	@endif

	<p><a href="{{ url('manuals') }}">Back to manuals</a></p>

@endsection
