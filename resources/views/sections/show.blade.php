<!-- /resources/views/sections/show.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>{{ $section->section_name }}</h2>
	<h4>Total overview of all templates</h4>
 
    @if ( !$section->templates->count() )
        This section has no templates.
    @else
		<table class="table section-table dialog table-striped" border="1">
        
		<tr class="success">
		<td class="header">Template</td>
		<td class="header">Name</td>
		<td class="header">Type</td>
		<td class="header">Description</td>
		<td class="header" style="width: 245px;">Options</td>
		</tr>
            @foreach( $templates as $template )
                <tr>
                    {!! Form::open(array('class' => 'form-inline', 'method' => 'DELETE', 'route' => array('sections.templates.destroy', $template->section_id, $template->id), 'onsubmit' => 'return confirm(\'Are you sure to delete this template?\')')) !!}
                        <td><a href="{{ route('sections.templates.show', [$section->id, $template->id]) }}">{{ $template->template_name }}</a></td>
						<td>{{ $template->template_shortdesc }}</td>
						<td>{{ $template->section_shortdesc }}</td>
						<td>{{ $template->template_longdesc }}</td>
						<td>
						<a class="btn btn-primary btn-xs" style="margin-left:2px;" href="{{ url('exporttemplate') . '/' . $template->id }}">Export</a>
						@if (!Auth::guest())
                            {!! link_to_route('sections.templates.edit', 'Edit', array($template->section_id, $template->id), array('class' => 'btn btn-info btn-xs')) !!}
							<a class="btn btn-warning btn-xs" style="margin-left:2px;" href="{{ url('templatestructure') . '/' . $template->id }}">Structure</a>
                            {!! Form::submit('Delete', array('class' => 'btn btn-danger btn-xs', 'style' => 'margin-left:2px;')) !!}
						@endif
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