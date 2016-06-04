<!-- /resources/views/tinymce/section.blade.php -->
<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

<script>tinymce.init({ 	
	selector:'textarea#section_description',
	valid_elements: "p[style],a[href|target],strong/b,i/em,br,table,tbody,thead,tr,td,ul,ol,li",
	height: 200,
	plugins: [
		'link image'
	],
	menubar: '',
	toolbar: 'undo redo | bold italic | link | bullist numlist',
	body_class: 'form-control',
	statusbar: false,
	content_style: "p {margin-top: -4px; color: #2c3e50; font-size: 15px; font-family: inherit ! important;}, span {font-family: inherit ! important;}, ol,ul"
});</script>

<script>tinymce.init({ 	
	selector:'textarea#section_longdesc',
	valid_elements: "p[style],a[href|target],strong/b,i/em,br,table,tbody,thead,tr,td,ul,ol,li",
	height: 300,
	plugins: [
		'link image'
	],
	menubar: '',
	toolbar: 'undo redo | alignleft aligncenter alignright | bold italic | link | bullist numlist',
	body_class: 'form-control',
	statusbar: false,
	content_style: "p {margin-top: -4px; color: #2c3e50; font-size: 15px; font-family: inherit ! important;}, span {font-family: inherit ! important;}, ol,ul"
});</script>