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
				'<p class="term-glossary">@{{glossary_name}}</p>',
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
	var cloneCount = {{ $term->objects->count() }};

	$('body').on('click', '.add-more', function(event) {
		//increase clone count
		cloneCount++;
		//temporary disable typeahead on all input dialogs
		$('input.typeahead').typeahead('destroy');

		//clone 
		$( "div.dropdown-relationships#0" ).clone().attr('id', cloneCount).appendTo( "div#relations" ).find("input[type='text']").val("");
		$( "div.dropdown-relationships#" + cloneCount + ' select').attr('name', 'Relations[' + cloneCount + '][relation_id]');
		$( "div.dropdown-relationships#" + cloneCount + ' input.hidden-object').attr('name', 'Relations[' + cloneCount + '][object_id]');

		//activate typeahead on all input dialogs
		myTypeahead();

		//add delete button
		$('div#' + cloneCount + ' div#last.col-md-2').append( "<span style=\"float:left; margin-left: 5px;\"><button type=\"button\" class=\"btn btn-danger remove\">-</button></span>" );
	});

	//function to delete div element when clicking on delete button
	$('body').on('click', '.remove', function(event) {
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
		{!! Form::select('owner_id', $owners->lists('username', 'id'), $term->owner_id, ['id' => 'owner_id', 'class' => 'form-control']) !!}
		</div>
	</div>

	@if ( $term->id )
		<div class="col-sm-2"></div>
		<div class="col-sm-10"><h4>Relations</h4></div>
		
		<input type="hidden" name="term_id" value="{{ $term->id }}">

		<!-- start div relations -->
		<div id="relations">

			@if ( $term->objects->count() )
				@foreach( $term->objects as $key => $object )
					<div id="{{ $key }}" class="row dropdown-relationships" style="margin-top: 4px;">
						<div id="first" class="col-md-2">
							<input class="hidden-object" type="hidden" name="Relations[{{ $key }}][object_id]" value="{{ $object->object_id }}">
						</div>
						<div class="col-md-3">
							<input class="form-control" id="disabledInput" type="text" placeholder="{{ $object->subject->term_name }}" disabled>
						</div>
						<div class="col-md-2">
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
						<div class="col-md-3">
							<input name="Relations[{{ $key }}][object_name]" class="form-control typeahead" value="{{ $object->object->term_name }}" type="text" data-provide="typeahead" autocomplete="off">
						</div>
						<div id="last" class="col-md-2">
							<span style="float:left; margin-left: 5px;"><button type="button" class="btn btn-default add-more">+</button></span>
							@if ($key > 0)
								<span style="float:left; margin-left: 5px;"><button type="button" class="btn btn-danger remove">-</button></span>
							@endif
						</div>
					</div>
				@endforeach
			@else
				<div id="0" class="row dropdown-relationships">
					<div id="first" class="col-md-2">
						<input class="hidden-object" type="hidden" name="Relations[0][object_id]" value="">
					</div>
					<div class="col-md-3">
						<input class="form-control" id="disabledInput" type="text" placeholder="{{ $term->term_name }}" disabled>
					</div>
					<div class="col-md-2">
						<select name="Relations[0][relation_id]" class="form-control">
							@foreach($relations as $relation)
								<option value="{{ $relation->id }}">{{ $relation->relation_name }}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3">
						<input name="Relations[0][object_name]" class="form-control typeahead" type="text" placeholder="Search for terms" data-provide="typeahead" autocomplete="off">
					</div>
					<div id="last" class="col-md-2">
						<span style="width: 30px; float:left; margin-left: 5px;"><button type="button" class="btn btn-default add-more">+</button></span>
					</div>
				</div>
			@endif

		<!-- end div relations -->
		</div>
	@endif

	<div class="form-group">
		{!! Form::submit($submit_text, ['class' => 'btn btn-primary']) !!}
	</div>

</div>
