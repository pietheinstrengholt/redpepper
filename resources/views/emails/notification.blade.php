<!-- /resources/views/emails/test.blade.php -->

<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="{{ URL::asset('css/bootstrap.min.css') }}">
</head>
<body>

<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
    <tr>
        <td align="center" valign="top">
            <table border="0" cellpadding="20" cellspacing="0" width="600" id="emailContainer">
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="20" cellspacing="0" width="100%" id="emailHeader">
                            <tr>
                                <td align="left" valign="top">
                                    <small>This is an automatically generated delivery status notification from the RADAR application.</small>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="20" cellspacing="0" width="100%" id="emailBody">
                            <tr>
                                <td align="left" valign="top">
                                    <h2>A change request has been submitted for review</h2>
									<h4><a href="{!! url('changerequests/' . $content_name . '/edit'); !!}">Click here</a> to review the change request</h4>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" valign="top">
                        <table border="0" cellpadding="20" cellspacing="0" width="100%" id="emailFooter">
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

