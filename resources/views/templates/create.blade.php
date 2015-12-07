<!-- /resources/views/templates/create.blade.php -->
@extends('layouts.master')

@section('content')
    <h2>Create Template</h2>
 
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
			<div class="col-sm-6">
			{!! Form::textarea('template_shortdesc', null, ['class' => 'form-control', 'rows' => '4']) !!}
			</div>
			
		</div>

		<div class="form-group">
			{!! Form::label('template_longdesc', 'Template longdesc:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-6">
			{!! Form::textarea('template_longdesc', null, ['class' => 'form-control', 'rows' => '7']) !!}
			</div>
		</div>
		
		<div class="form-group">
			{!! Form::label('section_id', 'Section:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-6">
			{!! Form::select('section_id', $sections->lists('section_name', 'id'), null, ['id' => 'section_id', 'class' => 'form-control']) !!}
			</div>
		</div>
		
		<div class="form-group">
			<label for="inputcolumns" class="col-sm-3 control-label">Number of Columns</label>
			<div class="col-sm-6">
			<input class="form-control" type="text" style="width: 50px;" name="inputcolumns" id="inputcolumns" placeholder="..">
			</div>
		</div>

		<div class="form-group">
			<label for="inputrows" class="col-sm-3 control-label">Number of Rows</label>
			<div class="col-sm-6">
			<input class="form-control" type="text" style="width: 50px;" name="inputrows" id="inputrows" placeholder="..">
			</div>
		</div>
		
		<div class="form-group">
			{!! Form::submit('Create new template', ['class' => 'btn btn-primary']) !!}
		</div>

	</div>
	<input type="hidden" name="_token" value="{!! csrf_token() !!}">
    {!! Form::close() !!}
@endsection

@stop