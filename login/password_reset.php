<?php
$pageIsPublic = true;
include("../include.php");

if ($posting) {
	if ($r = db_grab("SELECT id FROM users WHERE email = '{$_POST["email"]}' AND is_active = 1")) {
		emailUser($_POST["email"], "Reset Your Password", drawEmptyResult('To reset your password, please <a href="' . url_base() . '/login/password_reset.php?id=' . $r . '">follow this link</a>.'));
		url_change("password_confirm.php");
	} else {
		url_query_add(array("msg"=>"email-not-found", "email"=>$_POST["email"])); //bad email
	}
} elseif (url_id("id")) {
	$_SESSION["user_id"] = false;
	db_query("UPDATE users SET password = PWDENCRYPT('') WHERE id = {$_GET["id"]} AND is_active = 1");
	if ($email = db_grab("SELECT email FROM users WHERE id = {$_GET["id"]} AND is_active = 1")) {
		login($email, "", true);
		url_change($_SESSION["homepage"]);
	} else {
		url_change(false);
	}
} else {
	cookie("last_login");
	$_SESSION["user_id"] = false;
}
?>
<html>
	<head>
		<title>Reset Your Password</title>
		<link rel="stylesheet" type="text/css" href="/styles/screen.css" />
		<script language="javascript" src="/javascript.js"></script>
	</head>
	<body>
<br>
<table width="600" align="center">
	<tr>
		<td>
<?
if (@$_GET["msg"] == "email-not-found") {
	echo drawMessage("<h1>Email Not Found</h1>That email address wasn't found in the system.  If the address below is correct and you've never logged in, you may need to <a href='account_request.php'>request an account</a>.");
} else {
	echo drawMessage("<h1>Starting Over, Password-Wise</h1>Your old password can't be recovered, since it was encrypted.  However, it can be reset so you can pick a new one.  What is the email address on the account?");
}

$form = new intranet_form;
$form->addRow("itext", "Email", "email", @$_GET["email"], "", true, 50);
$form->addRow("submit", "Send Request");
$form->draw("Reset Password");
?>
		</td>
	</tr>
</table>
	</body>
</html>