<?
//this one is not public
include("../include.php");

if ($_POST) {
	db_query("UPDATE intranet_users SET password = PWDENCRYPT('{$_POST["password1"]}') WHERE userID = " . $_SESSION["user_id"]);
	$r = db_grab("SELECT p.url homepage FROM intranet_users u JOIN pages p ON u.homePageID = p.id WHERE u.userID = " . $_SESSION["user_id"]);
	url_change($r);
}
?>
<html>
	<head>
		<title>Update Your Password</title>
		<link rel="stylesheet" type="text/css" href="<?=$locale?>style.css" />
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