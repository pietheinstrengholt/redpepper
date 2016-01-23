<!-- /resources/views/settings/index.blade.php -->
@extends('layouts.master')

@section('content')

<ul class="breadcrumb breadcrumb-section">
<li><a href="{!! url('/'); !!}">Home</a></li>
<li class="active">Settings</li>
</ul>

@if (count($errors) > 0)
	<div class="alert alert-danger">
	<ul>
	@foreach ($errors->all() as $error)
		<li>{{ $error }}</li>
	@endforeach
	</ul>
	</div>
@endif

<h2>Settings</h2>
<h4>Manage configuration settings</h4><br>

{!! Form::open(array('action' => 'SettingController@store', 'id' => 'form')) !!}

<div class="form-group">
  <label for="usr">Bank name:</label>
  <input name="bank_name" type="text" style="width:550px;" class="form-control" id="usr" value="{{ $config_array['bank_name'] }}" placeholder="{{ $config_array['bank_name'] }}">
</div>

<div class="form-group">
  <label for="usr">Field property1:</label>
  <input name="fieldname_property1" type="text" style="width:550px;" class="form-control" id="usr" value="{{ $config_array['fieldname_property1'] }}" placeholder="{{ $config_array['fieldname_property1'] }}">
</div>

<div class="form-group">
  <label for="usr">Field property2:</label>
  <input name="fieldname_property2" type="text" style="width:550px;" class="form-control" id="usr" value="{{ $config_array['fieldname_property2'] }}" placeholder="{{ $config_array['fieldname_property2'] }}">
</div>

<div class="form-group">
  <label for="usr">Administrator email message:</label>
  <input name="administrator_email" type="email" style="width:550px;" class="form-control" id="usr" value="{{ $config_array['administrator_email'] }}" placeholder="{{ $config_array['administrator_email'] }}">
</div>

<h5>Allow super admin to process changes directly:</h5>
<select name="superadmin_process_directly" class="form-control" style="width: 100px;" id="visible">
@if (!empty($config_array['superadmin_process_directly']))
	@if ($config_array['superadmin_process_directly'] == "yes") 
		<option value="yes" selected="selected">Yes</option> 
	@else 
		<option value="yes">Yes</option> 
	@endif
	@if ($config_array['superadmin_process_directly'] == "no")
		<option value="no" selected="selected">No</option> 
	@else 
		<option value="no">No</option> 
	@endif
@else
	<option value="yes">Yes</option>
	<option value="no">No</option>
@endif
</select><br><br>

<button style="margin-bottom:15px;" type="submit" class="btn btn-primary">Submit new settings</button>

<input type="hidden" name="_token" value="{!! csrf_token() !!}">
{!! Form::close() !!}

@endsection