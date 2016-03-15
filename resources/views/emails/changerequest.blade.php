<!-- /resources/views/emails/changerequest.blade.php -->

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<style type="text/css">
	html {
		font-family: sans-serif;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
	}
	body {
		font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
		font-size: 14px;
		line-height: 1.42857143;
		color: #333;
		background-color: #fff;
		margin: 0;
	}
	h1, h2,	h3, h4 {
	  font-family: inherit;
	  font-weight: 500;
	  line-height: 1.1;
	  color: inherit;
	}
	h1 {
		font-size: 36px;
	}
	h2 {
		font-size: 30px;
	}
	h3 {
		font-size: 24px;
	}
	h4 {
		font-size: 18px;
	}
	small {
		font-size: 80%;
	}
	a, a:visited {
		text-decoration: underline;
		color: #337ab7;
		text-decoration: none;
		background-color: transparent;
	}
	a:focus {
		color: #23527c;
		text-decoration: underline;
	}
	</style>
</head>
<body>
<h2>RADAR Changerequest notification</h2>
<h4>A change request has been submitted for template {{ $template_name }} by {{ $username }}. The changerequest is pending for review</h4>
<h4><a href="{!! url('changerequests/' . $changerequest_id . '/edit'); !!}">Click here</a> to review the change request</h4>
<small>If you have any questions, please send an email to: <a href="mailto:frc@nl.abnamro.com">FRC RADAR</A></small>
</body>