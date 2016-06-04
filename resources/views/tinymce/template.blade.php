<!-- /resources/views/tinymce/template.blade.php -->
<script src="{{ URL::asset('js/tinymce/tinymce.min.js') }}"></script>

<script>tinymce.init({ 	
	selector:'textarea#template_shortdesc',
	valid_elements: "p[style],h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,br,table[*],tbody[*],thead[*],tr[*],td[*],ul,ol,li,img[src|height|width]",
	height: 200,
	plugins: [
		'link image imageupload'
	],
	menubar: '',
	toolbar: 'undo redo | bold italic | link | bullist numlist',
	relative_urls: false,
	body_class: 'form-control',
	statusbar: false,
	style_format_merge: true,
	content_style: "p {margin-top: -4px; color: #2c3e50; font-size: 15px; font-family: inherit ! important;}, span {font-family: inherit ! important;}, ol,ul"
});</script>

<script>tinymce.init({ 	
	selector:'textarea#template_longdesc',
	valid_elements: "p[style],h1,h2,h3,h4,h5,a[href|target],strong/b,i/em,br,table[*],tbody[*],thead[*],tr[*],td[*],ul,ol,li,img[src|height|width]",
	height: 400,
	plugins: [
		'link image imageupload table nonbreaking'
	],
	style_formats : [
		{title : 'Heading 1', block : 'h1'},
		{title : 'Heading 2', block : 'h2'},
		{title : 'Heading 3', block : 'h3'},
		{title : 'Heading 4', block : 'h4'},
		{title : 'Heading 5', block : 'h5'},
	],
	menubar: '',
	toolbar: 'undo redo | alignleft aligncenter alignright | styleselect | bold italic | outdent indent | link | imageupload | bullist numlist | table | nonbreaking',
	relative_urls: false,
	body_class: 'form-control',
	statusbar: false,
	style_format_merge: true,
	content_style: "p {margin-top: -4px; color: #2c3e50; font-size: 15px; font-family: inherit ! important;}, span {font-family: inherit ! important;}, ol,ul"
});</script>