<!-- /resources/views/types/show.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li><a href="{!! url('/types'); !!}">Types</a></li>
	<li class="active">{{ $type->type_name }}</li>
	</ul>

	<h2>Types descriptions</h2>
	<h4>This type has the following content</h4>

	@if ( !$descriptions->count() )
		<br>No content has been uploaded to the database!<br><br>
	@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Content</td>
		<td class="header">Description</td>
		</tr>

		@foreach( $descriptions as $description )
			<tr>
			<td>{{ $description->content }}</td>
			<td>{{ $description->description }}</td>
			</tr>
		@endforeach

		</table>
	@endif

@endsection
