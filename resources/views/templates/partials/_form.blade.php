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
		<div class="col-sm-8">
		{!! Form::textarea('template_shortdesc', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>

	</div>

	<div class="form-group">
		{!! Form::label('template_longdesc', 'Template longdesc:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-8">
		{!! Form::textarea('template_longdesc', null, ['id' => 'template_longdesc', 'class' => 'form-control', 'rows' => '7']) !!}
		</div>
	</div>

	{{-- Only show fields below when the template has rows and columns --}}
	@if ( $template->rows->count() && $template->columns->count() )
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
	@endif

	<div class="form-group">
		{!! Form::label('row_header_desc', 'Optional row header description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-5">
		{!! Form::text('row_header_desc', null, array('class' => 'form-control', 'placeholder' => 'Enter a optional description to replace the row description in the header')) !!}
		</div>
	</div>

	{{-- Only allow superadmin to change the section of a template --}}
	@if (Auth::user()->role == "superadmin")
		@if ( !($template->parent) )
		<div class="form-group">
			{!! Form::label('section_id', 'Section:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-5">
			{!! Form::select('section_id', $sections->lists('section_name', 'id'), $template->section_id, ['id' => 'section_id', 'class' => 'form-control']) !!}
			</div>
		</div>
		@endif
	@else
		<input type="hidden" name="section_id" value="{{ $section->id }}">
	@endif

	{{-- If the template does not have any children, show drop down below --}}
	@if ( !($template->children->count()) )
		<div class="form-group">
			{!! Form::label('parent_id', 'Optional parent:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-5">
			{!! Form::select('parent_id', $templates->lists('template_name', 'id'), $template->parent_id, ['id' => 'parent_id', 'placeholder' => '', 'class' => 'form-control']) !!}
			</div>
		</div>
	@endif

	<div class="form-group">
		{!! Form::label('type_id', 'Linked to types:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-5">
		{!! Form::select('type_id', $types->lists('type_name', 'id'), $template->type_id, ['id' => 'type_id', 'placeholder' => '', 'class' => 'form-control']) !!}
		</div>
	</div>

	@if ((Auth::user()->id != $template->created_by && Auth::user()->role == "builder") || Auth::user()->role == "superadmin")
		<div class="form-group">
			{!! Form::label('visible', 'Visible:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-5">
			{!! Form::select('visible', ['True' => 'Yes, all users can see this template', 'False' => 'No, only visible for (super)admin, builder users'], $template->visible, ['id' => 'visible', 'class' => 'form-control']) !!}
			</div>
		</div>
	@endif

	<div class="form-group">
		{!! Form::label('template_type', 'Template option:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-5">
		{!! Form::select('template_type', [null => 'Normal template, with no restrictions', 'non-clickable' => 'Non-clickable template, do not show pop-ups when clicking cells'], $template->template_type, ['id' => 'template_type', 'class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
