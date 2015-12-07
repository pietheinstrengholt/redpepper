<!-- /resources/views/departments/edit.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Edit Department "{{ $department->department_name }}"</h2>
 
    {!! Form::model($department, ['method' => 'PATCH', 'route' => ['departments.update', $department->id]]) !!}
        @include('departments/partials/_form', ['submit_text' => 'Edit Department'])
    {!! Form::close() !!}
@endsection

@stop