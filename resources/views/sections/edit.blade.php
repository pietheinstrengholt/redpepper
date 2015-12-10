<!-- /resources/views/sections/edit.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Edit Section "{{ $section->section_name }}"</h2>
	
	@if (count($errors) > 0)
		<div class="alert alert-danger">
			<ul>
				@foreach ($errors->all() as $error)
					<li>{{ $error }}</li>
				@endforeach
			</ul>
		</div>
	@endif	
 
    {!! Form::model($section, ['method' => 'PATCH', 'route' => ['sections.update', $section->id]]) !!}
        @include('sections/partials/_form', ['submit_text' => 'Edit Section'])
    {!! Form::close() !!}
@endsection

@stop