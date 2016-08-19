<!-- /resources/views/modal.blade.php -->

<h2>Latest changes</h2>
<h4>The table below represents an overview of the latest updates</h4>

@if ( !$logs->count() )
	No logs have been found in the database!<br><br>
@else
	<table class="table section-table dialog table-striped table-condensed" border="1">

	<tr class="success">
	<td class="header">Event</td>
	<td class="header" style="width: 20%;">User</td>
	<td class="header">Created</td>
	</tr>

	@foreach( $logs as $log )
		@if ($log->action == "Updated" || $log->action == "Approved")
			<tr id="{{ $log->id }}">
			<td>
			@if ($log->log_event == "Changerequest")
				<a href="{!! url('/') . '/changerequests/' . $log->changerequest_id . '/edit'; !!}">Changerequest {{ $log->changerequest_id }}</a> has been approved {{ $log->template_id }}
				@if ($log->template)
					for template <a href="{!! url('/') . '/sections/' . $log->template->section_id . '/templates/' . $log->template_id; !!}">{{ $log->template->template_name }}</a>
				@endif
			@endif
			@if ($log->log_event == "Template")
				@if ($log->template)
					Template <a href="{!! url('/') . '/sections/' . $log->template->section_id . '/templates/' . $log->template_id; !!}">{{ $log->template->template_name }}</a> has been updated.
				@endif
			@endif
			</td>
			<td>
			@if ($log->creator)
				{{ $log->creator->firstname }} {{ $log->creator->lastname }} ({{ $log->creator->username }})
			@endif
			</td>
			<td>{{ $log->created_at }}</td>
			</tr>
		@endif
	@endforeach

	</table>
@endif
