<!-- /resources/views/templates/modal.blade.php -->
<!-- Modal pop-up -->
<div class="modal fade" id="template-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title"><b>Field information dialog</b></h4>
			</div>
			<div class="modal-body">
				<div id="modalContent">
				</div>
			</div>
			<div class="modal-footer">
				@can('create-changerequest', $template)
					<button type="button" id="modal-update" class="btn btn-warning">Change content</button>
				@endcan
				<button type="button" id="modal-close" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
