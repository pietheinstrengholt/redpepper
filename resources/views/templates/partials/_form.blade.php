<!-- /resources/views/templates/partials/_form.blade.php -->
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
		{!! Form::textarea('template_longdesc', null, ['id' => 'template_longdesc', 'class' => 'form-control', 'rows' => '7']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('frequency_description', 'Frequency description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('frequency_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('reporting_dates_description', 'Reporting dates description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('reporting_dates_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('main_changes_description', 'Main changes description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('main_changes_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('links_other_temp_description', 'Links other temp description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('links_other_temp_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('process_and_organisation_description', 'Process and organisation description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('process_and_organisation_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('section_id', 'Section:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('section_id', $sections->lists('section_name', 'id'), $template->section_id, ['id' => 'section_id', 'class' => 'form-control']) !!}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('type_id', 'Type:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		
		<select class="form-control">
		<option selected value="0" disabled>Please select one option</option>
		@foreach($types as $type)
			@if ( $template->type_id == $type->id )
				<option selected value="{{ $type->id }}">{{ $type->type_name }}</option>
			@else
				<option value="{{ $type->id }}">{{ $type->type_name }}</option>
			@endif
		@endforeach
		</select>
		</div>
	</div>
	
	@if (Auth::user()->role == "admin")
		{!! Form::hidden('visible','False') !!}
	@endif
	
	@if (Auth::user()->role == "builder" || Auth::user()->role == "superadmin")
		<div class="form-group">
			{!! Form::label('visible', 'Visible:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-6">
			{!! Form::select('visible', ['True' => 'True', 'Limited' => 'Only show descriptions', 'False' => 'False'], $template->visible, ['id' => 'visible', 'class' => 'form-control']) !!}
			</div>
		</div>
	@endif

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
