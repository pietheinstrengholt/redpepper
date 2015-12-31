<!-- /resources/views/errors/excelupload.blade.php -->
@extends('layouts.master')

@section('content')

	<h2>Whoops! It looks like something went wrong when importing the Excel file</h2>
	<h4>Please review the errors below carefully. Correct the Excel and please try again.</h4>

	@if (count($errors) > 0)
		<div class="alert alert-danger">
		<ul>
		@foreach ($errors as $error)
			<li>{{ $error }}</li>
		@endforeach
		</ul>
		</div>
	@endif

	@if (empty($templatestructure['columns']) || empty($templatestructure['rows']))
		Error: This template has no columns or no rows.
	@else
		<strong>Template structure</strong>
		<table class="table table-bordered template" border="1">

		<!-- Table header with column names -->
		<thead>
		<tr class="success">

		<td class="header content">Row#</td>
		<td class="header">Row description</td>

		@foreach( $templatestructure['columns'] as $column )
			@if (array_key_exists('error', $column))
				<td class="content header error" id="$column['column_code']">
			@else
				<td class="content header" id="$column['column_code']">
			@endif
			{{ $column['column_description'] }}
			</td>
		@endforeach
		</tr>

		<!-- Table header with column nums -->

		<tr class="header2">

		<td></td>
		<td></td>

		@foreach( $templatestructure['columns'] as $column )
			@if (array_key_exists('error', $column))
				<td class="column error">{{ $column['column_code'] }}</td>
			@else
				<td class="column">{{ $column['column_code'] }}</td>
			@endif
		@endforeach

		</tr>
		</thead>

		<!-- Table content with row information -->
		<tbody>
		@foreach( $templatestructure['rows'] as $row )

			<tr>
			@if (array_key_exists('error', $row))
				<td class="desc error">{{ $row['row_code'] }}</td>
				<td class="desc error property{{ $row['row_reference'] }}">{{ $row['row_description'] }}</td>
			@else
				<td class="desc">{{ $row['row_code'] }}</td>
				<td class="desc property{{ $row['row_reference'] }}">{{ $row['row_description'] }}</td>
			@endif

			<!-- Table cell information, column and row combination -->
			@foreach( $templatestructure['columns'] as $column )

				<!-- Create a new variable, column and row combination -->
				{{--*/ $field = 'column' . $column['column_code'] . '-row' . $row['row_code'] /*--}}

				@if (array_key_exists($field, $arraydisabled))
					<td title="{{ $column['column_description'] }} - {{ $row['row_description'] }}" class="disabled" id="{{ $field }}"></td>
				@else
					<td title="{{ $column['column_description'] }} - {{ $row['row_description'] }}" class="tablecell" id="{{ $field }}">
				@endif

			@endforeach
			</tr>

		@endforeach
		</tbody>

		</table>

    @endif

	@if (!empty($templatestructure['column_content']))
		<strong>Column content</strong>
		<table class="table table-bordered template" style="width:70%" border="1">
		<tr class="success">
		<td class="header">column_code</td>
		<td class="header">content_type</td>
		<td class="header">content</td>
		</tr>

		@foreach($templatestructure['column_content'] as $row)
			@if (array_key_exists('error', $row))
				<tr class="error">
			@else
				<tr>
			@endif
			<td>{{ $row['column_code'] }}</td>
			<td>{{ $row['content_type'] }}</td>
			<td>{{ $row['content'] }}</td>
			</tr>
		@endforeach
		</table>
    @endif

	@if (!empty($templatestructure['row_content']))
		<strong>Column content</strong>
		<table class="table table-bordered template" style="width:70%" border="1">
		<tr class="success">
		<td class="header">row_code</td>
		<td class="header">content_type</td>
		<td class="header">content</td>
		</tr>

		@foreach($templatestructure['row_content'] as $row)
			@if (array_key_exists('error', $row))
				<tr class="error">
			@else
				<tr>
			@endif
			<td>{{ $row['row_code'] }}</td>
			<td>{{ $row['content_type'] }}</td>
			<td>{{ $row['content'] }}</td>
			</tr>
		@endforeach
		</table>
    @endif

	@if (!empty($templatestructure['field_content']))
		<strong>Field content</strong>
		<table class="table table-bordered template" style="width:80%" border="1">
		<tr class="success">
		<td class="header">column_code</td>
		<td class="header">row_code</td>
		<td class="header">content_type</td>
		<td class="header">content</td>
		</tr>

		@foreach($templatestructure['field_content'] as $row)
			@if (array_key_exists('error', $row))
				<tr class="error">
			@else
				<tr>
			@endif
			<td>{{ $row['column_code'] }}</td>
			<td>{{ $row['row_code'] }}</td>
			<td>{{ $row['content_type'] }}</td>
			<td>{{ $row['content'] }}</td>
			</tr>
		@endforeach
		</table>
    @endif

	@if (!empty($templatestructure['sourcing']))
		<strong>Sourcing</strong>
		<table class="table table-bordered template" style="width:95%" border="1">
		<tr class="success">
		<td class="header">column_code</td>
		<td class="header">row_code</td>
		<td class="header">type</td>
		<td class="header">source</td>
		<td class="header">value</td>
		<td class="header">description</td>
		</tr>

		@foreach($templatestructure['sourcing'] as $row)
			@if (array_key_exists('error', $row))
				<tr class="error">
			@else
				<tr>
			@endif
			<td>{{ $row['column_code'] }}</td>
			<td>{{ $row['row_code'] }}</td>
			<td>{{ $row['type'] }}</td>
			<td>{{ $row['source'] }}</td>
			<td>{{ $row['value'] }}</td>
			<td>{{ $row['description'] }}</td>
			</tr>
		@endforeach
		</table>
    @endif

	@if (!empty($templatestructure['template_content']))
		<strong>Template attributes</strong>
		<table class="table table-bordered template" style="width:65%" border="1">
		<tr class="success">
		<td class="header">attribute</td>
		<td class="header">description</td>
		</tr>

		@foreach($templatestructure['template_content'] as $key => $row)
			@if (array_key_exists('error', $templatestructure['template_content']))
				<tr class="error">
			@else
				<tr>
			@endif
			<td>{{ $key }}</td>
			<td>{{ $row }}</td>
			</tr>
		@endforeach
		</table>
    @endif

@endsection
