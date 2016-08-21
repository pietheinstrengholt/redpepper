<!-- /resources/views/csv/seeids.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Id's overview</li>
	</ul>

	<h2>Id's overview</h2>
	<h4>The tables below show the id's corresponding to the templates, sources and types</h4>

	<div class="row">
		<div class="col-md-4">
			<table class="table section-table dialog table-striped" border="1">
			<tr class="success">
			<td class="header">template_id</td>
			<td class="header">section_name</td>
			<td class="header">template_name</td>
			</tr>
			@foreach ($templates as $template)
				<tr>
				<td>{{ $template->id }}</td>
				<td>{{ $template->section->section_name }}</td>
				<td>{{ $template->template_name }}</td>
				</tr>
			@endforeach
			</table>
		</div>
		<div class="col-md-1">
		</div>
		<div class="col-md-3">
			<table class="table section-table dialog table-striped" border="1">
			<tr class="success">
			<td class="header">source_id</td>
			<td class="header">source_name</td>
			</tr>
			@foreach ($sources as $source)
				<tr>
				<td>{{ $source->id }}</td>
				<td>{{ $source->source_name }}</td>
				</tr>
			@endforeach
			</table>
		</div>
		<div class="col-md-1">
		</div>
		<div class="col-md-3">
			<table class="table section-table dialog table-striped" border="1">
			<tr class="success">
			<td class="header">type_id</td>
			<td class="header">type_name</td>
			</tr>
			@foreach ($types as $type)
				<tr>
				<td>{{ $type->id }}</td>
				<td>{{ $type->type_name }}</td>
				</tr>
			@endforeach
			</table>
		</div>
	</div>

@endsection
