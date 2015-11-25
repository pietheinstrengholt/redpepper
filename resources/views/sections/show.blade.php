<!-- /resources/views/sections/show.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>{{ $section->section_name }}</h2>
	<h4>Total overview of all templates</h4>
 
    @if ( !$section->templates->count() )
        This section has no templates.
    @else
		<table style="margin-bottom:20px;" class="table section-table dialog table-striped" border="1">
        
		<tr class="success">
		<td class="header">Template</td>
		<td class="header">Name</td>
		<td class="header">Type</td>
		<td class="header">Description</td>
		<td class="header" style="width: 120px;">Options</td>
		</tr>
            @foreach( $section->templates as $template )
                <tr>
                    {!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('sections.templates.destroy', $section->id, $template->id))) !!}
                        <td><a href="{{ route('sections.templates.show', [$section->id, $template->id]) }}">{{ $template->template_name }}</a></td>
						<td>{{ $template->template_shortdesc }}</td>
						<td>{{ $section->name }}</td>
						<td>{{ $template->template_longdesc }}</td>
						<td>
                            {!! link_to_route('sections.templates.edit', 'Edit', array($section->id, $template->id), array('class' => 'btn btn-info btn-xs')) !!}
                            {!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:3px;')) !!}
						</td>
                    {!! Form::close() !!}
                </tr>
            @endforeach
		</table>
    @endif
 
    <p>
        {!! link_to_route('sections.index', 'Back to Sections') !!} |
        {!! link_to_route('sections.templates.create', 'Create Template', $template->id) !!}
    </p>

@endsection

@stop