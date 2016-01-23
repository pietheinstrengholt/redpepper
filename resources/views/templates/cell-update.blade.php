<!-- /resources/views/template/cell-update.blade.php -->
@extends('layouts.master')

@section('content')

	<div id="cell-update">

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors->all() as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	{!! Form::open(array('action' => 'ChangeRequestController@create', 'id' => 'form')) !!}

	<!-- hidden form data -->
	<input name="column_code" type="hidden" value="{{ $column->column_code }}"/>
	<input name="row_code" type="hidden" value="{{ $row->row_code }}"/>
	<input name="section_id" type="hidden" value="{{ $template->section_id }}"/>
	<input name="template_id" type="hidden" value="{{ $template->id }}"/>
	<input name="username_id" type="hidden" value="1"/>

	<!-- cell content -->
	<table class="table dialog table-striped">
	<tr>
	<td class="info-header"><h4><b>Specific Information: {{ $row['row_code'] }} - {{ $column['column_code'] }}</b></h4></td>
	</tr>
	<td>

	<dl class="dl-horizontal">
	<dt>{!! App\Helper::setting('fieldname_property1') !!}:</dt>
	<dd><textarea form="form" name="field_property1" class="form-control" rows="1" id="field_property1">{{ $field_property1['content'] }}</textarea></dd>
	</dl>

	<dl class="dl-horizontal">
	<dt>{!! App\Helper::setting('fieldname_property2') !!}:</dt>
	<dd><textarea form="form" name="field_property2" class="form-control" rows="1" id="field_property2">{{ $field_property2['content'] }}</textarea></dd>
	</dl>

	<dl class="dl-horizontal">
	<dt>Regulation:</dt>
	<dd><textarea form="form" name="field_regulation" class="form-control" rows="5" id="field_regulation">{{ $field_regulation['content'] }}</textarea></dd>
	</dl>

	<dl class="dl-horizontal">
	<dt>Interpretation:</dt>
	<dd><textarea form="form" name="field_interpretation" class="form-control" rows="5" id="field_interpretation">{{ $field_interpretation['content'] }}</textarea></dd>
	</dl>

	</td>
	</table>

	<!-- legal and interpretations content -->
	<table class="table dialog table-striped">
	<tr>
		<td class="info-header"><h4><b>Row information: {{ $row['row_code'] }}</b></h4></td>
		<td class="info-header"><h4><b>Column information: {{ $column['column_code'] }}</b></h4></td>
	</tr>

	<tr>
		<td class="info-left im-content">
			<h4><b>Name:</b></h4>
			<div rows="1" id="rowname">{{ $row['row_description'] }}</div>
			<h4><b>Regulation:</b></h4>
			<textarea form="form" name="regulation_row" class="form-control" rows="7" id="regulation_row">{{ $regulation_row['content'] }}</textarea>
			<h4><b>Interpretation:</b></h4>
			<textarea form="form" name="interpretation_row" class="form-control" rows="6" id="interpretation_row">{{ $interpretation_row['content'] }}</textarea>
		</td>
		<td class="info-right im-content">
			<h4><b>Name:</b></h4>
			<div rows="1" id="colname">{{ $column['column_description'] }}</div>
			<h4><b>Regulation:</b></h4>
			<textarea form="form" name="regulation_column" class="form-control" rows="7" id="regulation_column">{{ $regulation_column['content'] }}</textarea>
			<h4><b>Interpretation:</b></h4>
			<textarea form="form" name="interpretation_column" class="form-control" rows="6" id="interpretation_column">{{ $interpretation_column['content'] }}</textarea>
		</td>
	</tr>
	</table>

	<!-- technical content -->
	<table class="table dialog table-striped">
		<tr>
			<td class="info-header"><h4><b>Technical information</b></h4></td>
		</tr>
		<td>
			<!-- IE8+ requires to wrap the form with an additional div element -->
			<div id="showRole">
				<table class="table" id="technical" border="1">
					<tr class="success">
						<th class="source">System</th>
						<th class="type">Type</th>
						<th class="content">Value</th>
						<th class="description">Description</th>
						<th class="action">Action</th>
					</tr>

					@if ( !$technical->count() )
					<tr>
						<td class="source">
							<select name="technical[1000][source_id]" class="form-control">
							<option>...</option>
							@foreach( $sources as $source )
								<option value="{{ $source->id }}">{{ $source->source_name }}</option>
							@endforeach
							</select>
						</td>
						<td class="type">
						<select name="technical[1000][type_id]" class="form-control">
							<option>...</option>
							@foreach( $types as $type )
								<option value="{{ $type->id }}">{{ $type->type_name }}</option>
							@endforeach
							</select>
						</td>
						<td class="content">
							<input name="technical[1000][content]" type="text" class="form-control" value="..." placeholder="...">
						</td>
						<td class="description">
							<input name="technical[1000][description]" type="text" class="form-control" value="..." placeholder="...">
						</td>
						<td class="action">
							<div class="checkbox">
								<label>
								<input name="technical[1000][action]" type="checkbox" value="delete">Delete
								<input name="end" value="endofrow" type="hidden">
								</label>
							</div>
						</td>
					</tr>

					@else
						<?php $countrows = 1; ?>
						@foreach( $technical as $row )
							<tr id="{{ $row->id }}">
								<td class="source">
									<select name="technical[{{ $countrows }}][source_id]" class="form-control">
									@foreach( $sources as $source )
										@if ($row->source_id == $source->id)
											<option selected value="{{ $source->id }}">{{ $source->source_name }}</option>
										@else
											<option value="{{ $source->id }}">{{ $source->source_name }}</option>
										@endif
									@endforeach
									</select>
								</td>
								<td class="type">
									<select name="technical[{{ $countrows }}][type_id]" class="form-control">
									@foreach( $types as $type )
										@if ($row->type_id == $type->id)
											<option selected value="{{ $type->id }}">{{ $type->type_name }}</option>
										@else
											<option value="{{ $type->id }}">{{ $type->type_name }}</option>
										@endif
									@endforeach
									</select>
								</td>

								<td class="content">
									<input name="technical[{{ $countrows }}][content]" type="text" class="form-control" value="{{ $row->content }}" placeholder="{{ $row->content }}">
								</td>

								<td class="description">
									<input name="technical[{{ $countrows }}][description]" type="text" class="form-control" value="{{ $row->description }}" placeholder="{{ $row->description }}">
								</td>
								<td class="action">
									<div class="checkbox">
										<label>
										<input name="technical[{{ $countrows }}][action]" type="checkbox" value="delete">Delete
										<input name="end" value="endofrow" type="hidden">
										</label>
									</div>
								</td>
							</tr>
							<?php $countrows++; ?>
						@endforeach
					@endif


					<!-- end of when technical information is found -->
					<!-- following code is needed to hide a new row, this line will be copied with js when the add new row button is pressed -->
					<tr id="newline" style="display:none;" class="newlines">
						<td class="source">
							<select name="technical[hidden][source_id]" class="form-control">
							<option>...</option>
									@foreach( $sources as $source )
										<option value="{{ $source->id }}">{{ $source->source_name }}</option>
									@endforeach
							</select>
						</td>
						<td class="type">
							<select name="technical[hidden][type_id]" class="form-control">
							<option>...</option>
									@foreach( $types as $type )
										<option value="{{ $type->id }}">{{ $type->type_name }}</option>
									@endforeach
							</select>
						</td>

						<td class="content">
							<input name="technical[hidden][content]" type="text" class="form-control" value="..." placeholder="...">
						</td>

						<td class="description">
							<input name="technical[hidden][description]" type="text" class="form-control" value="..." placeholder="...">
						</td>
						<td class="action">
							<div class="checkbox">
								<label>
								<input name="technical[hidden][action]" type="checkbox" value="delete">Delete
								<input name="end" value="endofrow" type="hidden">
								</label>
							</div>
							<input name="technical[hidden][hidden]" type="hidden" value="yes"/>
						</td>
					</tr>
					<!-- end of new line -->
				</table>
			</div>

			<button id="addnewrow" type="button" class="btn btn-info btn-sm">Add new row</button><br><br>

			</div>

		</td>
	</table>

	<button class="btn btn-primary" id="approve-changes" name="formSubmit" value="Submit" type="submit">Submit changes</button>
</form>

@endsection
