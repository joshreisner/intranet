<?php
include('include.php');

lib_get('swiftmailer');

$transport	= Swift_SmtpTransport::newInstance('server289.com', 25)->setUsername('members')->setPassword('smtpbot');
$mailer		= Swift_Mailer::newInstance($transport);
$message	= Swift_Message::newInstance()
	->setSubject('Test SwiftMailer')
	->setFrom(array('members@sitesofconscience.org'=>'Online Resource Center'))
	->setTo(array('josh@joshreisner.com'=>'Josh Reisner'))
	->setBody('Here is the message itself')
	->addPart('<h1>Hi There</h1><p>Here is the message itself</p>', 'text/html')
	//->attach(Swift_Attachment::fromPath('my-document.pdf'))
;

//Pass a variable name to the send() method
if (!$mailer->batchSend($message, $failures)) {
	echo 'Failures:';
	print_r($failures);
} else {
	echo 'mailer sent without failures';
}


/*
lib_get('phpmailer');

//mailserver connection
$mailer = new PHPMailer();
$mailer->IsSMTP();
$mailer->Host		= 'server289.com';
$mailer->SMTPAuth = true;
$mailer->Username = 'members';
$mailer->Password = 'smtpbot';
$mailer->port = 25;
$mailer->From = 'members@sitesofconscience.org';
$mailer->FromName = 'Members Mailer';
$mailer->Sender = 'members@sitesofconscience.org';
$mailer->Priority = 3;
$mailer->CharSet = 'UTF-8';

//email message
$mailer->Subject = 'This is another test';
$mailer->Body = 'This is a test of my mail system! ' . format_date();
$mailer->AddAddress('josh@joshreisner.com', 'Josh Reisner');
$mailer->AddAddress('josh@joshreisner.com', 'Josh Reisner');

//exec
echo ($mailer->Send()) ? 'Mail sent!' : 'There was a problem sending this mail!';

//clean up
$mailer->ClearAddresses();
$mailer->ClearAttachments();
*/
?>