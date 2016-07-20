<!-- /resources/views/tinymce/section.blade.php -->
<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

<script>tinymce.init({ 	
	selector:'textarea#section_longdesc',
valid_elements: "p,h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,u,br,table[*],tbody[*],thead[*],tr[*],td[*],ul,ol,li,img[src|height|width]",
	formats : {
		underline : {inline : 'u', exact : true}
	},
	height: 300,
	plugins: [
		'link image table nonbreaking searchreplace'
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
	toolbar: 'undo redo | alignleft aligncenter alignright | styleselect | bold italic underline | outdent indent | link | bullist numlist | table | nonbreaking  | searchreplace',
	relative_urls: false,
	statusbar: false,
	style_format_merge: true,
	content_css: ["{!! URL::asset('css') . '/' . App\Helper::setting('css_style') !!}"],
	content_style: "body {margin: 10px ! important; }",
});</script>