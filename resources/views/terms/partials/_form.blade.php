<!-- /resources/views/terms/partials/_form.blade.php -->

<script src="{{ URL::asset('js/handlebars.js') }}"></script>
<script src="{{ URL::asset('js/typeahead.bundle.js') }}"></script>

<script type="text/javascript">

function myTypeahead() {
	//set url
	var myRegex = /.+?(?=index.php)/;
	var myUrl = myRegex.exec(window.location.href);

	var haunt, repos, sources;
	repos = new Bloodhound({
		datumTokenizer: function(d) { return Bloodhound.tokenizers.whitespace(d.value); },
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		limit: 100,
		/* prefetch: {
			name: 'terms',
			url: myUrl[0] + 'index.php/api/terms',
		} */
		remote: {
			url: myUrl[0] + 'index.php/api/terms?search=%QUERY',
			wildcard: '%QUERY'
		}
	});

	//initialize data
	repos.initialize();

	$('input.typeahead').typeahead(null, {
		name: 'repos',
		source: repos.ttAdapter(),
		templates: {
			empty: '<div class="term-box" ><p class="term-glossary"></p><p style="margin-left:10px; color:red;" class="term-tername"> No matches</p><p class="term-description"></p></div>',
			suggestion: Handlebars.compile([
				'<div class="term-box" id="@{{id}}">',
				'<p style="color:#f48024;" class="term-glossary">@{{glossary_name}}</p>',
				'<p class="term-termname">@{{term_name}}</p>',
				'<p class="term-description">@{{term_description}}</p>',
				'</div>'
			].join(''))
		}
	});
}

$("document").ready(function(){

	//clear typeahead cache
	localStorage.clear();

	//destroy typeahead
	$('input.typeahead').typeahead('destroy');
	$('input.searcheahead').typeahead('destroy');

	//set clone count to the number of current relations
	var objectsCount = {{ $term->objects->count() }};
	var propertiesCount = {{ $term->properties->count() }};

	$('body').on('click', '.object-add-more', function(event) {
		//increase objects count
		objectsCount++;
		//temporary disable typeahead on all input dialogs
		$('input.typeahead').typeahead('destroy');

		//clone 
		$("div.dropdown-relationships#0").clone().attr('id', objectsCount).appendTo("div#relations-wrapper").find("input[type='text']").val("");
		$("div.dropdown-relationships#" + objectsCount + ' select').attr('name', 'Relations[' + objectsCount + '][relation_id]');
		$("div.dropdown-relationships#" + objectsCount + ' input.hidden-object').attr('name', 'Relations[' + objectsCount + '][object_id]');

		//activate typeahead on all input dialogs
		myTypeahead();

		//add delete button
		$('div.dropdown-relationships#' + objectsCount + ' div#last.col-md-1').append("<span><button style=\"margin-top: 10px;\" type=\"button\" class=\"btn btn-danger btn-xs object-remove\">remove</button></span>");
	});

	$('body').on('click', '.property-add-more', function(event) {
		//increase properties count
		propertiesCount++;
		console.log(propertiesCount);

		//clone 
		$("div#0.row.dropdown-properties").clone().attr('id', propertiesCount).appendTo("div#properties").find("input[type='text']").val("");
		$("div#" + propertiesCount + ".row.dropdown-properties" + " input#property_name.form-control").attr('name', 'Properties[' + propertiesCount + '][property_name]');
		$("div#" + propertiesCount + ".row.dropdown-properties" + " input#property_value.form-control").attr('name', 'Properties[' + propertiesCount + '][property_value]');

		//add delete button
		$('div.dropdown-properties#' + propertiesCount + ' div#last.col-md-1').append("<span><button style=\"margin-top: 10px;\" type=\"button\" class=\"btn btn-danger btn-xs property-remove\">remove</button></span>");
	});

	//function to delete div element when clicking on delete button
	$('body').on('click', '.object-remove', function(event) {
		$(this).closest("div.row").remove();
	});

	//function to delete div element when clicking on delete button
	$('body').on('click', '.property-remove', function(event) {
		$(this).closest("div.row").remove();
	});

	//function when clicking on term, set id
	$('body').on('click', '.term-box', function(event) {
		//get term id
		var object_id = $(this).attr('id');
		//get id from upper div
		var row_id = $(this).closest("div.dropdown-relationships").attr('id');
		//set input with id from term
		$('div#' + row_id + '.dropdown-relationships input.hidden-object').attr("value",object_id);
	});

	//initialize typeahead ion initial load
	myTypeahead();
});
</script>

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

	<div class="form-group">
		{!! Form::label('glossary_id', 'Glossary:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('glossary_id', $glossaries->lists('glossary_name', 'id'), $term->glossary_id, ['id' => 'glossary_id', 'class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('status_id', 'Status:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		{!! Form::select('status_id', $statuses->lists('status_name', 'id'), $term->status_id, ['id' => 'status_id', 'class' => 'form-control']) !!}
		</div>
	</div>

	<div class="form-group">
		{!! Form::label('owner_id', 'Owner:', array('class' => 'col-sm-3 control-label')) !!}
		<div class="col-sm-6">
		@if ( $term->id )
			{!! Form::select('owner_id', $owners->lists('username', 'id'), $term->owner_id, ['id' => 'owner_id', 'class' => 'form-control']) !!}
		@else
			{!! Form::select('owner_id', $owners->lists('username', 'id'), Auth::user()->id, ['id' => 'owner_id', 'class' => 'form-control']) !!}
			<input type="hidden" name="created_by" value="{{ Auth::user()->id }}">
		@endif
		</div>
	</div>
	
	<div class="col-sm-1"></div>

	@if ( $term->id )
		<div style="background-color: #f7f7f9; border: 1px solid #e1e1e8; margin-bottom: 10px;" class="col-sm-11">
		<input type="hidden" name="term_id" value="{{ $term->id }}">

		<!-- start div relations -->
		<div class="term" id="relations">
			<div class="term" id="relations-wrapper">
			<h4>Relations</h4>

				@if ( $term->objects->count() )
					@foreach( $term->objects as $key => $object )
						<div id="{{ $key }}" class="row dropdown-relationships" style="margin-top: 4px;">
							<input class="hidden-object" type="hidden" name="Relations[{{ $key }}][object_id]" value="{{ $object->object_id }}">
							<div class="col-md-3">
								<input class="form-control" id="disabledInput" type="text" placeholder="{{ $object->subject->term_name }}" disabled>
							</div>
							<div class="col-md-3">
								<select name="Relations[{{ $key }}][relation_id]" class="form-control">
								@foreach($relations as $relation)
									@if ($object->relation_id == $relation->id)
										<option selected="selected" value="{{ $relation->id }}">{{ $relation->relation_name }}</option>
									@else
										<option value="{{ $relation->id }}">{{ $relation->relation_name }}</option>
									@endif
								@endforeach
								</select>
							</div>
							<div class="col-md-5">
								<input name="Relations[{{ $key }}][object_name]" class="form-control typeahead" value="{{ $object->object->term_name }}" type="text" data-provide="typeahead" autocomplete="off">
							</div>
							<div id="last" style="padding-left: 0px;" class="col-md-1">
								@if ($key > 0)
									<span><button type="button" style="margin-top: 10px;" class="btn btn-danger btn-xs object-remove">remove</button></span>
								@endif
							</div>
						</div>
					@endforeach
				@else
					<div id="0" class="row dropdown-relationships">
						<input class="hidden-object" type="hidden" name="Relations[0][object_id]" value="">
						<div class="col-md-3">
							<input class="form-control" id="disabledInput" type="text" placeholder="{{ $term->term_name }}" disabled>
						</div>
						<div class="col-md-3">
							<select name="Relations[0][relation_id]" class="form-control">
								@foreach($relations as $relation)
									<option value="{{ $relation->id }}">{{ $relation->relation_name }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-5">
							<input name="Relations[0][object_name]" class="form-control typeahead" type="text" placeholder="Search for terms" data-provide="typeahead" autocomplete="off">
						</div>
						<div id="last" style="padding-left: 0px;" class="col-md-1">
						</div>
					</div>
				@endif

			<!-- end div relations -->
			</div>
			<span><button type="button" style="margin-top:5px;" class="btn btn-success btn-xs object-add-more">add new relation</button></span>
		</div>

		<!-- start div relations -->
		<div style="margin-top: 20px;" class="term" id="properties">
			<div class="term" id="properties-wrapper">
			<h4>Properties</h4>

				@if ( $term->properties->count() )
					@foreach( $term->properties as $key => $property )
						<div id="{{ $key }}" class="row dropdown-properties" style="margin-top: 4px;">
							<div class="col-md-5">
								<input id="property_name" name="Properties[{{ $key }}][property_name]" class="form-control" type="text" value="{{ $property->property_name }}">
							</div>
							<div class="col-md-6">
								<input id="property_value" name="Properties[{{ $key }}][property_value]" class="form-control" type="text" value="{{ $property->property_value }}">
							</div>
							<div id="last" style="padding-left: 0px;" class="col-md-1">
								@if ($key > 0)
									<span><button style="margin-top: 10px;" type="button" class="btn btn-danger btn-xs property-remove">remove</button></span>
								@endif
							</div>
						</div>
					@endforeach
				@else
					<div id="0" class="row dropdown-properties" style="margin-top: 4px;">
						<div class="col-md-5">
							<input id="property_name" name="Properties[0][property_name]" class="form-control" type="text" placeholder="Enter property name">
						</div>
						<div class="col-md-6">
							<input id="property_value" name="Properties[0][property_value]" class="form-control" type="text" placeholder="Enter property value">
						</div>
						<div id="last" style="padding-left: 0px;" class="col-md-1">
						</div>
					</div>
				@endif

			<!-- end div relations -->
			</div>
		</div>
		<span><button type="button" style="margin-top:5px; margin-bottom: 10px;" class="btn btn-success btn-xs property-add-more">add new property</button></span>
	</div>
	@endif

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
