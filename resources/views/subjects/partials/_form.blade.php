<!-- /resources/views/subjects/partials/_form.blade.php -->
<div class="form-horizontal">

	<div class="form-group">
		{!! Form::label('subject_name', 'Building block name:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::text('subject_name', null, ['class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('subject_description', 'Building block description:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('subject_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('subject_longdesc', 'Building block longdesc:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::textarea('subject_longdesc', null, ['id' => 'subject_longdesc', 'class' => 'form-control', 'rows' => '7']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('visible', 'Visible:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('visible', ['True' => 'Yes, all users can see this subject', 'False' => 'No, only visible for (super)admin, builder users'], $subject->visible, ['id' => 'visible', 'class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('subject_order', 'Aditional label to order:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-1">
		{!! Form::text('subject_order', $subject->subject_order, ['id' => 'subject_order', 'class' => 'form-control', 'rows' => '1']) !!}
		</div>
	</div>

	{{-- If the template does not have any children, show drop down below --}}
	@if ( !($subject->children->count()) )
		<div class="form-group">
			{!! Form::label('parent_id', 'Optional parent:', array('class' => 'col-sm-3 control-label')) !!}
			<div class="col-sm-5">
			{!! Form::select('parent_id', $subjects->lists('subject_name', 'id'), $subject->parent_id, ['id' => 'parent_id', 'placeholder' => '', 'class' => 'form-control']) !!}
			</div>
		</div>
	@endif

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
