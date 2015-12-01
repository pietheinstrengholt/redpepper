<!-- /resources/views/users/partials/_form.blade.php -->
<div class="form-horizontal">

	<div class="form-group">
		{!! Form::label('username', 'Username:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::text('username', null, ['class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('firstname', 'First name:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::text('firstname', null, ['class' => 'form-control']) !!}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('lastname', 'Last name:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::text('lastname', null, ['class' => 'form-control']) !!}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('email', 'Email address:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::text('email', null, ['class' => 'form-control']) !!}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('department_id', 'Department:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('department_id', $departments->lists('department_name', 'id'), null, ['id' => 'department_id', 'class' => 'form-control']) !!}
		</div>
	</div>
	
	<div class="form-group">
		{!! Form::label('role', 'Role:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('role', array('superadmin' => 'superadmin', 'admin' => 'admin', 'builder' => 'builder', 'contributor' => 'contributor'), null, ['id' => 'role', 'class' => 'form-control']) !!}
		</div>
	</div>		
	 
	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>