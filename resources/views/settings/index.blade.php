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
  <input name="bank_name" type="text" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('bank_name') !!}" placeholder="{!! Settings::get('bank_name') !!}">
</div>

<div class="form-group">
  <label for="usr">Field property1:</label>
  <input name="fieldname_property1" type="text" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('fieldname_property1') !!}" placeholder="{!! Settings::get('fieldname_property1') !!}">
</div>

<div class="form-group">
  <label for="usr">Field property2:</label>
  <input name="fieldname_property2" type="text" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('fieldname_property2') !!}" placeholder="{!! Settings::get('fieldname_property2') !!}">
</div>

<div class="form-group">
  <label for="usr">Welcome screen header text:</label>
  <input name="main_message1" type="text" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('main_message1') !!}" placeholder="{!! Settings::get('main_message1') !!}">
</div>

<div class="form-group">
  <label for="usr">Welcome screen sub text:</label>
  <input name="main_message2" type="text" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('main_message2') !!}" placeholder="{!! Settings::get('main_message2') !!}">
</div>

<div class="form-group">
  <label for="usr">Tool name:</label>
  <input name="tool_name" type="text" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('tool_name') !!}" placeholder="{!! Settings::get('tool_name') !!}">
</div>

<div class="form-group">
  <label for="usr">Administrator email message:</label>
  <input name="administrator_email" type="email" style="width:550px;" class="form-control" id="usr" value="{!! Settings::get('administrator_email') !!}" placeholder="{!! Settings::get('administrator_email') !!}">
</div>

<h5><label for="usr">Allow super admin to process changes directly:</label></h5>
<select name="superadmin_process_directly" class="form-control" style="width: 100px; margin-top: -10px;" id="visible">
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
</select>

@if (empty($config_array['css_style']))
	{{--*/ $css_style = '' /*--}}
@else
	{{--*/ $css_style = $config_array['css_style'] /*--}}
@endif

<h5><label for="usr">Select CSS layout:</label></h5>
<div style="width:300px; margin-top: -10px;">
{!! Form::select('css_style', $scanned_css_directory, $css_style, ['id' => 'css_style', 'class' => 'form-control']) !!}
</div>

@if (empty($config_array['homescreen_image']))
	{{--*/ $homescreen_image = '' /*--}}
@else
	{{--*/ $homescreen_image = $config_array['homescreen_image'] /*--}}
@endif

<h5><label for="usr">Select background image on homescreen:</label></h5>
<div style="width:300px; margin-top: -10px;">
{!! Form::select('homescreen_image', $scanned_img_directory, $homescreen_image, ['id' => 'homescreen_image', 'class' => 'form-control']) !!}
</div>

<button style="margin-bottom:15px; margin-top:20px;" type="submit" class="btn btn-primary">Submit new settings</button>

<input type="hidden" name="_token" value="{!! csrf_token() !!}">
{!! Form::close() !!}

@endsection
