<!-- /resources/views/terms/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	<li class="active">Terms</li>
	</ul>

	<h2>Terms</h2>
	<h4>Please make a selection of one of the following terms</h4>

	@if ( !$terms->count() )
		No terms found in the database!<br><br>
	@else
		<table class="table section-table dialog table-striped" border="1">

		<tr class="success">
		<td class="header">Term name</td>
		<td class="header">Definition</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>

		@foreach( $terms as $term )
			<tr>
			<td><a href="{!! route('terms.show',['term'=>$term]); !!}">{{ $term->term_name }}</a></td>
			<td>{{ $term->term_description }}</td>
			<td>
			@if (!Auth::guest())
				{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('terms.destroy', $term->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this term?\')')) !!}
				{!! link_to_route('terms.edit', 'Edit', array($term->id), array('class' => 'btn btn-info btn-xs')) !!}
				{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
				{!! Form::close() !!}
			@endif
			</td>

			</tr>
		@endforeach

		</table>
	@endif

	@if (Auth::check())
		<p>
		{!! link_to_route('terms.create', 'Create Term') !!}
		</p>
	@endif

	@if ( !empty($letters) )
		<div class="text-center">
		<ul class="pagination">
		@foreach( $letters as $page )
			@if ($page == $letter)
				<li class="active"><a href="{{ url()->current() }}?letter={{ $page }}">{{ $page }}</a></li>
			@else
				<li><a href="{{ url()->current() }}?letter={{ $page }}">{{ $page }}</a></li>
			@endif
		@endforeach
		</ul>
		</div>
	@endif

@endsection
