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

<h5>Bank name:</h5>
<textarea form="form" name="bank_name" rows="1" style="width:600px;" id="bank_name" class="form-control name">
@if (!empty($config_array['bank_name']))
{{ $config_array['bank_name'] }}
@endif
</textarea>
<br>

<h5>Field property1:</h5>
<textarea form="form" name="fieldname_property1" rows="1" style="width:550px;" id="fieldname_property1" class="form-control name">
@if (!empty($config_array['fieldname_property1']))
{{ $config_array['fieldname_property1'] }}
@endif
</textarea>
<br>

<h5>Field property2:</h5>
<textarea form="form" name="fieldname_property2" rows="1" style="width:550px;" id="fieldname_property2" class="form-control name">
@if (!empty($config_array['fieldname_property2']))
{{ $config_array['fieldname_property2'] }}
@endif
</textarea>
<br>

<h5>Administrator email message:</h5>
<textarea form="form" name="administrator_email" rows="1" style="width:500px;" id="administrator_email" class="form-control name">
@if (!empty($config_array['administrator_email']))
{{ $config_array['administrator_email'] }}
@endif
</textarea>
<br>

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