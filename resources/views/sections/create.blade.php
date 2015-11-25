<!-- /resources/views/sections/create.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Create Section</h2>
 
    {!! Form::model(new App\Section, ['route' => ['sections.store']]) !!}
        @include('sections/partials/_form', ['submit_text' => 'Create Section'])
    {!! Form::close() !!}
@endsection

@stop