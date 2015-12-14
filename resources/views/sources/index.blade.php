<!-- /resources/views/sources/index.blade.php -->
@extends('layouts.master')

@section('content')

	<ul class="breadcrumb breadcrumb-section">
	  <li><a href="{!! url('/'); !!}">Home</a></li>
	  <li class="active">Sources</li>
	</ul>

  <h2>Sources</h2>
	<h4>Please make a selection of one of the following sources</h4>

  @if ( !$sources->count() )
      No sources found in the database!<br><br>
  @else
	<table class="table section-table dialog table-striped" border="1">

	<tr class="success">
	<td class="header">Name</td>
	<td class="header">Description</td>
	<td class="header" style="width: 120px;">Options</td>
	</tr>

	@foreach( $sources as $source )
	<tr>
	<td>{{ $source->source_name }}</td>
	<td>{{ $source->source_description }}</td>
	{!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('sources.destroy', $source->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this source?\')')) !!}
	<td>
		{!! link_to_route('sources.edit', 'Edit', array($source->id), array('class' => 'btn btn-info btn-xs')) !!}
		{!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
	</td>
	{!! Form::close() !!}
	</tr>
	@endforeach

	</table>
  @endif

  <p>
  {!! link_to_route('sources.create', 'Create Source') !!}
  </p>

@endsection

@stop
