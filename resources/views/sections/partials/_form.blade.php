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
		<div class="col-sm-6">
		{!! Form::textarea('section_longdesc', null, ['class' => 'form-control', 'rows' => '7']) !!}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('subject_id', 'Section:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('subject_id', ['1' => 'Corep','2' => 'Finrep','3' => 'Liquidity', '4' => 'Other'], null, ['id' => 'subject_id', 'class' => 'form-control']) !!}
		</div>
	</div>	

	<div class="form-group">
		{!! Form::label('Visible', 'Visible:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6" style="margin-top: 11px;">
		@if ( $section->visible == "True" )
			{!! Form::checkbox('visible', 'True', true) !!}
		@else
			{!! Form::checkbox('visible', 'True') !!}		
		@endif
		</div>
	</div>
	 
	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>