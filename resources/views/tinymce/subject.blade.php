<!-- /resources/views/tinymce/subject.blade.php -->
<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
<script>tinymce.init({ 	
	selector:'textarea#subject_longdesc',
	height: 400,
	plugins: [
		'link image'
	],
	menubar: '',
	toolbar: 'undo redo | alignleft aligncenter alignright | bold italic | link | bullist numlist',
	body_class: 'form-control',
	statusbar: false,
	content_style: "p {margin-top: -4px; color: #2c3e50; font-size: 15px; font-family: inherit ! important;}, span {font-family: inherit ! important;}, ol,ul"
});</script>