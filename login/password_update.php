<?
//this one is not public
include("../include.php");

if ($posting) {
	db_query("UPDATE users SET password = PWDENCRYPT('{$_POST["password1"]}') WHERE user_id = " . $_SESSION["user_id"]);
	$_SESSION["password"] = false;
	url_change($_SESSION["homepage"]);
}
?>
<html>
	<head>
		<title>Update Your Password</title>
		<link rel="stylesheet" type="text/css" href="/styles/screen.css" />
		<script language="javascript" src="/javascript.js"></script>
	</head>
	<body>
<br>
<table width="600" align="center">
	<tr>
		<td>
<?
$form = new intranet_form;
$form->addRow("password", "Password", "password1", "", "", true);
$form->addRow("password", "Confirm", "password2", "", "", true);
$form->addRow("submit",   "Save");
$form->addJavascript("(form.password1.value != form.password2.value)", "Passwords don't match!");
$form->draw("Update Your Password");
?>
		</td>
	</tr>
</table>
	</body>
</html>