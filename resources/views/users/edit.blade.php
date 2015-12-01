<!-- /resources/views/users/edit.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Edit User "{{ $user->username }}"</h2>
 
    {!! Form::model($user, ['method' => 'PATCH', 'route' => ['users.update', $user->id]]) !!}
        @include('users/partials/_form', ['submit_text' => 'Edit User'])
    {!! Form::close() !!}
@endsection

@stop