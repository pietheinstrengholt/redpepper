<!-- /resources/views/termproperties/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	@if ( $termvalues )
		<li><a href="{!! url('/termproperties'); !!}">Term Properties</a></li>
		<li class="active">{{ $property_name }}</li>
	@else
		<li class="active">Term Properties</li>
	@endif
	</ul>

	@if ( $termvalues )
		<h2>{{ $property_name }}</h2>
		<h4>Please make a selection of one of the following values</h4>

		<table class="table section-table dialog table-striped" border="1">
		<tr class="success">
		<td class="header">Property value</td>
		<td class="header">Term name</td>
		<td class="header">Glossary name</td>
		</tr>
		@foreach( $termvalues as $termvalue )
			<tr>
			<td>{{ $termvalue->property_value }}</a></td>
			<td><a href="{!! url('terms'); !!}/{{ $termvalue->term->id }}">{{ $termvalue->term->term_name }}</a></td>
			<td><a href="{!! url('glossaries'); !!}/{{ $termvalue->term->glossary->id }}">{{ $termvalue->term->glossary->glossary_name }}</a></td>
			</tr>
		@endforeach
		</table>
	@else
		<h2>Term Properties</h2>
		<h4>Please make a selection of one of the following properties</h4>
		@if ( !$termproperties->count() )
			No term properties found in the database!<br><br>
		@else
			<table class="table section-table dialog table-striped" border="1">
			<tr class="success">
			<td class="header">Property name</td>
			</tr>

			@foreach( $termproperties as $termproperty )
				<tr>
				<td><a href="{!! url('termproperties'); !!}?property_name={{ $termproperty->property_name }}">{{ $termproperty->property_name }}</a></td>
				</tr>
			@endforeach

			</table>
		@endif
	@endif

@endsection
