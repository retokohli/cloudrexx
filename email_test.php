<?php
if (isset($_POST['email_to'])) {
	$emailFrom = get_magic_quotes_gpc() ? stripslashes($_POST['email_from']) : $_POST['email_from'];
	$emailTo = get_magic_quotes_gpc() ? stripslashes($_POST['email_to']) : $_POST['email_to'];
	@ob_start();
	$starttime = explode(' ', microtime());
	$status = mail(
		$emailTo,
		'E-Mail Test',
		'This E-Mail was sent to you by the purpose of testing the e-mail functionality.',
		'From: '.$emailFrom. "\r\n" . 'X-Mailer: PHP/' . phpversion()
	);
	$parsingtime = explode(' ', microtime());
	$time = round(((float)$parsingtime[0] + (float)$parsingtime[1]) - ((float)$starttime[0] + (float)$starttime[1]), 5);
	$error = @ob_get_clean();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>E-Mail Sending Test</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<p>
<?php
if (isset($status)) {
	if ($status) {
		print 'Test was successfull! Duration:'.$time;
	} else {
		print 'Test failed! There seems to be a problem with the SMTP service on this host!<br /><br />';
		print 'Reported error was: <blockquote>'.$error.'</blockquote>';
		print '<br />';
	}
} else {
	print 'E-mail functionality test';
    if (empty($emailFrom)) {
        $emailFrom = 'noreply@'.preg_replace('/^www\./', '', $_SERVER['HTTP_HOST']);
    }
}
?>
</p>
<form action="<?php print $_SERVER['PHP_SELF'];?>" method="POST">
	<label for="email_from">from</label> <input type="text" name="email_from" id="email_from" value="<?php print htmlentities($emailFrom, ENT_QUOTES, 'UTF-8');?>" /> <br />
	<label for="email_to">to</label> <input type="text" name="email_to" id="email_to" value="<?php print htmlentities($emailTo, ENT_QUOTES, 'UTF-8');?>" /> <br />
	<input type="submit" value="Start Test" />
</form>
</body>
</html>
