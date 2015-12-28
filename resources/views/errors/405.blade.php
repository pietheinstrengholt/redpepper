<!-- /resources/views/errors/405.blade.php -->
@extends('layouts.master')

@section('content')
	<h2>405 Error</h2>
	<div class="title">{{ $exception->getMessage() }}</div>
@endsection