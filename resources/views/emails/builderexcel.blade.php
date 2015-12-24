<!-- /resources/views/emails/test.blade.php -->

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

<table border="0" cellpadding="0" cellspacing="30" height="100%" width="100%" id="bodyTable">
	<tr>
		<td align="center" valign="top">
			<table border="0" cellpadding="10" cellspacing="0" width="600" id="emailContainer">
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="100%" id="emailHeader">
							<tr>
								<td align="left" valign="top" >
									<small>This is an automatically generated delivery status notification from the RADAR application.</small>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="100%" id="emailBody">
							<tr>
								<td align="left" valign="top">
								<h2>A new template {{ $content_name }} has been imported with Excel by {{ $username }}. The template is pending for approval.</h2>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td align="center" valign="top">
						<table border="0" cellpadding="0" cellspacing="0" width="100%" id="emailFooter">
							<tr>
								<td align="left" valign="top">
								If you have any questions, please send an email to: <a href="mailto:frc@nl.abnamro.com">FRC RADAR</A>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>

