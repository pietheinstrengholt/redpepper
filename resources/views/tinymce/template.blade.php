<!-- /resources/views/tinymce/template.blade.php -->
<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

<script>tinymce.init({
	selector:'textarea#template_shortdesc',
	valid_elements: "p,h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,u,br,table[*],tbody[*],thead[*],tr[*],td[*],ul,ol,li,img[src|height|width]",
	formats : {
		underline : {inline : 'u', exact : true}
	},
	height: 200,
	plugins: [
		'link image imageupload'
	],
	menubar: '',
	toolbar: 'undo redo | bold italic underline | link | bullist numlist',
	relative_urls: false,
	statusbar: false,
	style_format_merge: true,
	content_css: ["{!! URL::asset('css') . '/' . App\Helper::setting('css_style') !!}"],
	content_style: "body {margin: 10px ! important; }",
});</script>

<script>tinymce.init({
	selector:'textarea#template_longdesc',
	valid_elements: "p,h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,u,br,table[*],tbody[*],thead[*],tr[*],td[*],ul,ol,li,img[src|height|width]",
	formats : {
		underline : {inline : 'u', exact : true}
	},
	height: 400,
	plugins: [
		'link image imageupload table nonbreaking searchreplace'
	],
	style_formats : [
		{title : 'Heading 1', block : 'h1'},
		{title : 'Heading 2', block : 'h2'},
		{title : 'Heading 3', block : 'h3'},
		{title : 'Heading 4', block : 'h4'},
		{title : 'Heading 5', block : 'h5'},
		{title : 'Paragraph', block : 'p'},
	],
	menubar: '',
	toolbar: 'undo redo | alignleft aligncenter alignright | styleselect | bold italic underline | outdent indent | link | imageupload | bullist numlist | table | nonbreaking  | searchreplace',
	relative_urls: false,
	statusbar: false,
	style_format_merge: true,
	content_css: ["{!! URL::asset('css') . '/' . App\Helper::setting('css_style') !!}"],
	content_style: "body {margin: 10px ! important; }",
});</script>
