<!-- /resources/views/tinymce/subject.blade.php -->
<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>
<script>tinymce.init({ 	
	selector:'textarea#subject_longdesc',
	valid_elements: "p,a[href|target],strong/b,i/em,u,br,ul,ol,li",
	formats : {
		underline : {inline : 'u', exact : true}
	},
	height: 400,
	plugins: [
		'link image'
	],
	menubar: '',
	toolbar: 'undo redo | alignleft aligncenter alignright | bold italic underline | link | bullist numlist',
	statusbar: false,
	content_css: ["{!! URL::asset('css') . '/' . App\Helper::setting('css_style') !!}"],
	content_style: "body {margin: 10px ! important; }",
});</script>