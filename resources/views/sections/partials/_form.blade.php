<!-- /resources/views/sections/partials/_form.blade.php -->
<div class="form-horizontal">

	<div class="form-group">
		{!! Form::label('section_name', 'Section name:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::text('section_name', null, ['class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('section_description', 'Section Description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('section_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>

	</div>

	<div class="form-group">
		{!! Form::label('section_longdesc', 'Detailed description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-8">
		{!! Form::textarea('section_longdesc', null, ['class' => 'form-control', 'rows' => '7']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('subject_id', 'Section group:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-4">
		{!! Form::select('subject_id', $subjects->lists('subject_name', 'id'), $subject->id, ['id' => 'subject_id', 'class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('visible', 'Visible:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-4">
		{!! Form::select('visible', ['True' => 'Yes, all users can see this section', 'False' => 'No, only visible for (super)admin, builder users'], $section->visible, ['id' => 'visible', 'class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
