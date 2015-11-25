<!-- /resources/views/types/edit.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Edit Type "{{ $type->type_name }}"</h2>
 
    {!! Form::model($type, ['method' => 'PATCH', 'route' => ['types.update', $type->id]]) !!}
        @include('types/partials/_form', ['submit_text' => 'Edit Type'])
    {!! Form::close() !!}
@endsection

@stop