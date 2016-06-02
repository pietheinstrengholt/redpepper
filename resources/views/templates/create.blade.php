<!-- /resources/views/templates/create.blade.php -->
@extends('layouts.master')

@section('content')

	<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

	<script>tinymce.init({ 	
		selector:'textarea#template_shortdesc',
		valid_elements: "p[style],h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,br,table[style|border|cellspacing],tbody,thead,tr[style],td[style],ul,ol,li,img[src]",
		height: 200,
		plugins: [
			'link image imageupload'
		],
		menubar: '',
		toolbar: 'undo redo | bold italic | link | bullist numlist',
		relative_urls: false,
		body_class: 'form-control',
		statusbar: false,
		content_style: "p {margin-top: -4px;} ol,ul,p {color: #2c3e50; font-size: 15px;}"
	});</script>

	<script>tinymce.init({ 	
		selector:'textarea#template_longdesc',
		valid_elements: "p[style],h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,br,table[style|border|cellspacing],tbody,thead,tr[style],td[style],ul,ol,li,img[src]",
		height: 400,
		plugins: [
			'link image imageupload table'
		],
		style_formats : [
			{title : 'Heading 1', inline : 'h1', classes : 'h1'},
			{title : 'Heading 2', inline : 'h2', classes : 'h2'},
			{title : 'Heading 3', inline : 'h3', classes : 'h3'},
			{title : 'Heading 4', inline : 'h4', classes : 'h4'},
			{title : 'Heading 5', inline : 'h5', classes : 'h5'},
		],
		menubar: '',
		toolbar: 'undo redo | alignleft aligncenter alignright | styleselect | bold italic | outdent indent | link | imageupload | bullist numlist | table ',
		relative_urls: false,
		body_class: 'form-control',
		statusbar: false,
		content_style: "p {margin-top: -4px;} ol,ul,p {color: #2c3e50; font-size: 15px;}"
	});</script>
	
	<ul class="breadcrumb breadcrumb-section">
	<li><a href="{!! url('/'); !!}">Home</a></li>
	@if (!empty($section))
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
		
		@if (!empty($section))
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

		<div class="form-group">
			{!! Form::submit('Create new template', ['class' => 'btn btn-primary']) !!}
		</div>

	</div>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
	{!! Form::close() !!}
@endsection