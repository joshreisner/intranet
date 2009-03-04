<?php
$pageIsPublic = true;
include("../include.php");

if ($posting) {
	if ($r = db_grab("SELECT userID FROM intranet_users WHERE email = '{$_POST["email"]}' AND isActive = 1")) {
		email_user($_POST["email"], "Reset Your Password", drawEmptyResult('To reset your password, please <a href="' . url_base() . '/login/password_reset.php?id=' . $r . '">follow this link</a>.'));
		url_change("password_confirm.php");
	} else {
		url_query_add(array("msg"=>"email-not-found", "email"=>$_POST["email"])); //bad email
	}
} elseif (url_id("id")) {
	db_query("UPDATE intranet_users SET password = PWDENCRYPT('') WHERE userID = {$_GET["id"]} AND isActive = 1");
	if ($r = db_grab("SELECT u.email, p.url FROM intranet_users u LEFT JOIN pages p ON u.homePageID = p.ID WHERE u.userID = {$_GET["id"]} AND u.isActive = 1")) {
		//login($r["email"], "", true);
		cookie("last_login", $r["email"]);
		if (empty($r["url"])) $r["url"] = false;
		url_change($r["url"]);
	} else {
		url_change(false);
	}
}
cookie("last_login");

?>
<html>
	<head>
		<title>Reset Your Password</title>
		<link rel="stylesheet" type="text/css" href="<?=$locale?>style.css" />
		<script language="javascript" src="/javascript.js"></script>
	</head>
	<body>
<br>
<table width="600" align="center">
	<tr>
		<td>
<?
if (@$_GET["msg"] == "email-not-found") {
	echo drawServerMessage("<h1>Email Not Found</h1>That email address wasn't found in the system.  If the address below is correct and you've never logged in, you may need to <a href='account_request.php'>request an account</a>.");
} else {
	echo drawServerMessage("<h1>Starting Over, Password-Wise</h1>Your old password can't be recovered, since it was encrypted.  However, it can be reset so you can pick a new one.  What is the email address on the account?");
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