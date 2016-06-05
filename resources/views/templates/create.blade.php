<!-- /resources/views/templates/create.blade.php -->
@extends('layouts.master')

@section('content')

	@include('tinymce.template')

	<script>
	$( document ).ready(function() {
		$( "#mySelectBox" ).change(function() {
			var strSelect = "";
			$( "#mySelectBox option:selected" ).each(function() {
				strSelect += $(this).val();
			});

			if (strSelect == "document") {
				$("div#template").hide();
			}

			if (strSelect == "template") {
				$("div#template").show();
			}
		});
	});
	</script>
	
	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	@if (!empty($section) && $section->id != 0)
		<li><a href="{!! url('/sections'); !!}">Sections</a></li>
		<li><a href="{!! url('/sections/' . $section->id); !!}">{{ $section->section_name }}</a></li>
	@endif
	<li class="active">Create template</li>
	</ul>

	<h2>Create Template</h2>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'TemplateController@newtemplate', 'id' => 'form')) !!}

	<div class="form-horizontal">

		<div class="form-group">
			{!! Form::label('template_name', 'Template name:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-6">
			{!! Form::text('template_name', null, ['class' => 'form-control']) !!}
			</div>
		</div>

		<div class="form-group">
			{!! Form::label('template_shortdesc', 'Template shortdesc:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-8">
			{!! Form::textarea('template_shortdesc', null, ['class' => 'form-control', 'rows' => '4']) !!}
			</div>

		</div>

		<div class="form-group">
			{!! Form::label('template_longdesc', 'Template longdesc:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-8">
			{!! Form::textarea('template_longdesc', null, ['class' => 'form-control', 'rows' => '7']) !!}
			</div>
		</div>
		
		@if (!empty($section) && $section->id != 0)
			<input type="hidden" name="section_id" value="{{ $section->id }}">
			<div class="form-group">
				{!! Form::label('parent_id', 'Optional parent:', array('class' => 'col-sm-3 control-label')) !!}
				<div class="col-sm-5">
				{!! Form::select('parent_id', $templates->lists('template_name', 'id'), null, ['id' => 'parent_id', 'placeholder' => 'Optional parent', 'class' => 'form-control']) !!}
				</div>
			</div>
		@else
			<div class="form-group">
				{!! Form::label('section_id', 'Section:', array('class' => 'col-sm-3 control-label')) !!}
				<div class="col-sm-5">
				{!! Form::select('section_id', $sections->lists('section_name', 'id'), null, ['id' => 'section_id', 'class' => 'form-control']) !!}
				</div>
			</div>
		@endif

		@if (Auth::user()->role == "superadmin")
			<div class="form-group">
				{!! Form::label('visible', 'Visible:', array('class' => 'col-sm-3 control-label')) !!}
				<div class="col-sm-5">
				{!! Form::select('visible', ['True' => 'Yes, all users can see this template', 'False' => 'No, only visible for (super)admin, builder users'], null, ['id' => 'visible', 'class' => 'form-control']) !!}
				</div>
			</div>
		@else
			{!! Form::hidden('visible','False') !!}
		@endif
		
		<div class="form-group">
			{!! Form::label('visible', 'Template or document:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-5">
			<select name="template-type" id="mySelectBox" class="form-control">
				<option id="select-template" value="template">Create a template with rows and columns</option>
				<option id="select-document" value="document">Create a document without a table</option>
			</select>
			</div>
		</div>
		
		<div id="template">
			<div class="form-group">
				<label for="inputcolumns" class="col-sm-3 control-label">Number of Columns</label>
				<div class="col-sm-1">
				<input class="form-control" type="text" name="inputcolumns" id="inputcolumns" placeholder="....">
				</div>
			</div>

			<div class="form-group">
				<label for="inputrows" class="col-sm-3 control-label">Number of Rows</label>
				<div class="col-sm-1">
				<input class="form-control" type="text" name="inputrows" id="inputrows" placeholder="....">
				</div>
			</div>
		</div>

		<div class="form-group">
			{!! Form::submit('Create new template', ['class' => 'btn btn-primary']) !!}
		</div>

	</div>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}
@endsection
