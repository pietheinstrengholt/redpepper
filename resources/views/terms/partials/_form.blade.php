<!-- /resources/views/terms/partials/_form.blade.php -->

<div class="form-horizontal">

	<div class="form-group">
		{!! Form::label('term_name', 'Term name:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
			{!! Form::text('term_name', null, ['class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('term_description', 'Term definition:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
			{!! Form::textarea('term_description', null, ['class' => 'form-control', 'rows' => '4']) !!}
		</div>
	</div>

	@if ( $term->id )
		<input type="hidden" name="term_id" value="{{ $term->id }}">
	@endif

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
