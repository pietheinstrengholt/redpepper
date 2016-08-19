<html>
<head>
	<meta charset="UTF-8">
	<title>Image Upload</title>
	<script type="text/javascript" src="{{ URL::asset('js/tinymce/plugins/imageupload/upload.js') }}"></script>
	@if (!empty($file_path))
		<script type="text/javascript">
		window.parent.window.ImageUpload.uploadSuccess({
			code : '{{ $file_path }}'
		});
		</script>
		<style type="text/css">
			img {
				max-height: 240px;
				max-width: 320px;
			}
		</style>
	@endif
	@if (!empty($error))
		<script type="text/javascript">
		window.parent.window.ImageUpload.uploadError({
			code : '{{ $error }}'
		});
		</script>
	@endif
</head>
<body>
	@if (!empty($file_path))
		<img src="{{ $file_path }}">
	@endif
</body>
</html>
