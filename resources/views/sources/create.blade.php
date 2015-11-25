<!-- /resources/views/sections/create.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Create Source</h2>
 
    {!! Form::model(new App\TechnicalSource, ['route' => ['sources.store']]) !!}
        @include('sources/partials/_form', ['submit_text' => 'Create Source'])
    {!! Form::close() !!}
@endsection

@stop