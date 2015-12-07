<!-- /resources/views/template/cell-update.blade.php -->
@extends('layouts.master')

@section('content')

	<div style="margin-top:20px;">
	{!! Form::open(array('action' => 'ChangeRequestController@create', 'id' => 'form')) !!}

	<!-- hidden form data -->
	<input name="column_name" type="hidden" value="{{ $column->column_name }}"/>
	<input name="row_name" type="hidden" value="{{ $row->row_name }}"/>
	<input name="section_id" type="hidden" value="{{ $template->section_id }}"/>
	<input name="template_id" type="hidden" value="{{ $template->id }}"/>
	<input name="username_id" type="hidden" value="1"/>


	<!-- cell content -->
	<table style="border: 1px solid #ddd;" class="table dialog table-striped">
	<tr>
	<td class="info-header"><h4><b>Specific Information: {{ $row['row_name'] }} - {{ $column['column_name'] }}</b></h4></td>
	</tr>
	<td>

	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Fieldname property1:</dt>
	<dd><textarea form="form" name="field_property1" class="form-control" rows="1" id="field_property1" style="width: 50%;">{{ $field_property1['content'] }}</textarea></dd>
	</dl>

	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Fieldname property2:</dt>
	<dd><textarea form="form" name="field_property2" class="form-control" rows="1" id="field_property2" style="width: 50%;">{{ $field_property2['content'] }}</textarea></dd>
	</dl>

	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Regulation:</dt>
	<dd><textarea form="form" name="field_regulation" class="form-control" rows="5" id="field_regulation" style="width: 70%;">{{ $field_regulation['content'] }}</textarea></dd>
	</dl>

	<dl class="dl-horizontal" style="margin-right: 30px;">
	<dt>Interpretation:</dt>
	<dd><textarea form="form" name="field_interpretation" class="form-control" rows="5" id="field_interpretation" style="width: 70%;">{{ $field_interpretation['content'] }}</textarea></dd>
	</dl>

	</td>
	</table>


	<!-- legal and interpretations content -->
	<table style="border: 1px solid #ddd;" border="0" class="table dialog table-striped">
	<tr>
		<td class="info-header"><h4><b>Row information: {{ $row['row_name'] }}</b></h4></td>
		<td class="info-header"><h4><b>Column information: {{ $column['column_name'] }}</b></h4></td>
	</tr>

	<tr>
		<td class="info-left im-content">
			<h4><b>Name:</b></h4>
			<div rows="1" id="rowname">{{ $row['row_description'] }}</div>
			<h4><b>Regulation:</b></h4>
			<textarea form="form" name="regulation_row" class="form-control" rows="6" id="regulation_row" style="width: 90%;">{{ $regulation_row['content'] }}</textarea>
			<h4><b>Interpretation:</b></h4>
			<textarea form="form" name="interpretation_row" class="form-control" rows="5" id="interpretation_row" style="width: 90%;">{{ $interpretation_row['content'] }}</textarea>
		</td>
		<td class="info-right im-content">
			<h4><b>Name:</b></h4>
			<div rows="1" id="colname">{{ $column['column_description'] }}</div>
			<h4><b>Regulation:</b></h4>
			<textarea form="form" name="regulation_column" class="form-control" rows="6" id="regulation_column" style="width: 90%;">{{ $regulation_column['content'] }}</textarea>
			<h4><b>Interpretation:</b></h4>
			<textarea form="form" name="interpretation_column" class="form-control" rows="5" id="interpretation_column" style="width: 90%;">{{ $interpretation_column['content'] }}</textarea>
		</td>
	</tr>
	</table>

	<!-- technical content -->
	<?

	//technical
	echo "<table border=\"0\" class=\"table tech table-striped\">";
	echo "<td style=\"background-color:#fff ! important;\">";
	echo "<h4><b>Additional information:</b></h4>";
	echo "<p style=\"width: 100%;\">";
	//IE8+ requires to wrap the form with an additional div element
	echo "<div id=\"showRole\">";
	echo "<table style=\"width: 94%;\" class=\"table\" id=\"technical\" border=\"1\">";
	echo "<tr class=\"success\">";
	echo "<th>System</th>";
	echo "<th>Type</th>";
	echo "<th style=\"width: 30%;\">Value</th>";
	echo "<th>Description</th>";
	echo "<th>Action</th>";
	echo "</tr>";

	//no technical information is found
	if (empty($technical)) {
		echo "<tr>";
		echo "<td style=\"width:10%;\">";
		echo "<select name=\"technical[1000][source_id]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" class=\"form-control\">";
		echo "<option>...</option>";
		foreach($sources as $source) {
			$systemid = $source['id'];
			$name = $source['source_name'];
			echo "<option value=\"$systemid\">$name</option>";
		}
		echo "</select>";
		echo "</td>";
		echo "<td style=\"width:10%;\">";
		echo "<select name=\"technical[1000][type_id]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" class=\"form-control\">";
		echo "<option>...</option>";
		foreach($types as $type) {
			$techid = $type['id'];
			$name = $type['type_name'];
			echo "<option value=\"$techid\">$name</option>";
		}
		echo "</select>";
		echo "</td>";
		$tech_value = "...";
		echo "<td style=\"width:10%;\"><input name=\"technical[1000][content]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" type=\"text\" class=\"form-control\" id=\"tech_value\" value=\"$tech_value\" placeholder=\"$tech_value\"></td>";
		$tech_desc = "...";
		echo "<td><input name=\"technical[1000][description]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" type=\"text\" class=\"form-control\" id=\"tech_desc\" value=\"$tech_desc\" placeholder=\"$tech_desc\"></td>";
		echo "<td style=\"width:7%;\">";
		echo "<div class=\"checkbox\" style=\"margin-top:0px; margin-bottom:0px;\">";
		echo "<label>";
		echo "<span style=\"margin-right: 25px;\">Delete</span>";
		echo "<input name=\"technical[1000][action]\" type=\"checkbox\" value=\"delete\"><input name=\"end\" value=\"endofrow\" type=\"hidden\">";
		echo "</label>";
		echo "</div>";
		echo "</td>";
		echo "</tr>";
	//some technical information is found
	} else {
		$countrows = 1;
		foreach($technical as $row) {
		$trrowid = $row['id'];
		echo "<tr id=\"$trrowid\">";
		echo "<td style=\"width:10%;\">";
		echo "<select name=\"technical[$countrows][source_id]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" class=\"form-control\">";
		foreach($sources as $source) {
			$systemid = $source['id'];
			$name = $source['source_name'];
			//find source name and select as default
			if ($row['source_name'] == $source['source_name']) {
				echo "<option selected value=\"$systemid\">$name</option>";
			} else {
				echo "<option value=\"$systemid\">$name</option>";
			}
		}
		echo "</select>";
		echo "</td>";
		echo "<td style=\"width:10%;\">";
		echo "<select name=\"technical[$countrows][type_id]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" class=\"form-control\">";
		foreach($types as $type) {
			$typeid = $type['id'];
			$name = $type['type_name'];
			//find type name and select as default
			if ($row['type_name'] == $type['type_name']) {
			echo "<option selected value=\"$typeid\">$name</option>";
			} else {
				echo "<option value=\"$typeid\">$name</option>";
			}
		}
		echo "</select>";
		echo "</td>";
		$tech_value = $row['content'];
		echo "<td style=\"width:10%;\"><input name=\"technical[$countrows][content]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" type=\"text\" class=\"form-control\" id=\"tech_value\" value=\"$tech_value\" placeholder=\"$tech_value\"></td>";
		$tech_desc = $row['description'];
		echo "<td><input name=\"technical[$countrows][description]\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" type=\"text\" class=\"form-control\" id=\"tech_desc\" value=\"$tech_desc\" placeholder=\"$tech_desc\"></td>";
		echo "<td style=\"width:7%;\">";
		echo "<div class=\"checkbox\" style=\"margin-top:0px; margin-bottom:0px;\">";
		echo "<label>";
		echo "<span style=\"margin-right: 25px;\">Delete</span>";
		echo "<input name=\"technical[$countrows][action]\" type=\"checkbox\" value=\"delete\"><input name=\"end\" value=\"endofrow\" type=\"hidden\">";
		echo "</label>";
		echo "</div>";
		echo "</td>";
		echo "</tr>";
		$countrows++;
		}
	}
	//end of when technical information is found
	//following code is needed to hide a new row, this line will be copied with js when the add new row button is pressed
	echo "<tr id=\"newline\" style=\"display:none;\" class=\"newlines\">";
	echo "<td style=\"width:10%;\">";
	echo "<select name=\"technical[hidden][source_id]\" id=\"system_id\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" class=\"form-control\">";
	echo "<option>...</option>";
	foreach($sources as $source) {
		$systemid = $source['id'];
		$name = $source['source_name'];
		echo "<option value=\"$systemid\">$name</option>";
	}
	echo "</select>";
	echo "</td>";
	echo "<td style=\"width:10%;\">";
	echo "<select name=\"technical[hidden][type_id]\" id=\"type_id\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" class=\"form-control\">";
	echo "<option>...</option>";
	foreach($types as $type) {
		$typeid = $type['id'];
		$name = $type['type_name'];
		echo "<option value=\"$typeid\">$name</option>";
	}
	echo "</select>";
	echo "</td>";
	$tech_value = "...";
	echo "<td style=\"width:10%;\"><input name=\"technical[hidden][content]\" id=\"content\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" type=\"text\" class=\"form-control\" id=\"tech_value\" value=\"$tech_value\" placeholder=\"$tech_value\"></td>";
	$tech_desc = "...";
	echo "<td><input name=\"technical[hidden][description]\" id=\"description\" style=\"webkit-box-shadow: none; border: none; box-shadow: none; padding:0px; height:inherit;\" type=\"text\" class=\"form-control\" id=\"tech_desc\" value=\"$tech_desc\" placeholder=\"$tech_desc\"></td>";
	echo "<td style=\"width:7%;\">";
	echo "<div class=\"checkbox\" style=\"margin-top:0px; margin-bottom:0px;\">";
	echo "<label>";
	echo "<span style=\"margin-right: 25px;\">Delete</span>";
	echo "<input name=\"technical[hidden][action]\" id=\"action\" type=\"checkbox\" value=\"delete\"><input name=\"end\" value=\"endofrow\" type=\"hidden\">";
	echo "</label>";
	echo "</div>";
	echo "<input name=\"technical[hidden][hidden]\" type=\"hidden\" value=\"yes\"/>";
	echo "</td>";
	echo "</tr>";
	//end of new line
	echo "</div></table>";
	echo "<button id=\"addnewrow\" type=\"button\" class=\"btn btn-info\">Add new row</button><br><br>";
	echo "</td>";
	echo "</hr>";
	echo "</p><br>";
	echo "</table>";

	//add button and end form
	echo "<button class=\"btn btn-primary\" id=\"approve-changes\" name=\"formSubmit\" value=\"Submit\" type=\"submit\">Submit changes</button>";
	echo "</form>";
	echo "</div>";

	?>

@endsection

@stop