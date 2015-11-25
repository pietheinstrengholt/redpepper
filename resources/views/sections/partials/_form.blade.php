<div class="form-group">
    {!! Form::label('section_name', 'Name:') !!}
    {!! Form::text('section_name') !!}
</div>

<div class="form-group">
    {!! Form::label('section_description', 'Description:') !!}
    {!! Form::text('section_description') !!}
</div>

<div class="form-group">
    {!! Form::label('section_longdesc', 'Detailed description:') !!}
    {!! Form::text('section_longdesc') !!}
</div>

<div class="form-group">
    {!! Form::label('visible', 'Visible:') !!}
    {!! Form::checkbox('visible') !!}
</div>

<div class="form-group">
    {!! Form::submit($submit_text, ['class'=>'btn primary']) !!}
</div>