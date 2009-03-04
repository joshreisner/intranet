<?php
$pageIsPublic = true;
include("../include.php");

if ($posting) {

	$_POST["email"] = format_email($_POST["email"]);
	$_POST["phone"] = format_phone($_POST["phone"]);

	//create request
	//todo ~ check whether staff already exists -- forward to password reset
	if ($id = db_grab("SELECT userID FROM intranet_users WHERE email = '" . $_POST["email"] . "' AND isActive = 1")) {
		url_change("account_exists.php");
	} elseif ($id = db_grab("SELECT id FROM users_requests WHERE email = '" . $_POST["email"] . "'")) {
		db_query("UPDATE users_requests SET
			firstname = '" . $_POST["firstname"] . "', 
			lastname = '" . $_POST["lastname"] . "',
			nickname = '" . $_POST["nickname"] . "',
			title = '" . $_POST["title"] . "',
			phone = '" . $_POST["phone"] . "',
			email = '" . $_POST["email"] . "',
			departmentID = '" . $_POST["departmentID"] . "',
			corporationID = '" . $_POST["corporationID"] . "',
			officeID = '" . $_POST["officeID"] . "',
			bio = '" . $_POST["bio"] . "',
			createdOn = GETDATE()
		WHERE email = '" . $_POST["email"] . "'");
		email_user($_josh["email_admin"], "Repeat Account Request", drawEmptyResult($_POST["firstname"] . " " . $_POST["lastname"] . ' is <a href="http://' . $request["host"] . '/staff/add_edit.php?requestID=' . $id . '">re-requesting an account</a>.'));
	} else {
		$id = db_query("INSERT INTO users_requests (
			firstname, 
			lastname,
			nickname,
			title,
			phone,
			email,
			departmentID,
			corporationID,
			officeID,
			bio,
			createdOn
		) VALUES (
			'" . $_POST["firstname"] . "', 
			'" . $_POST["lastname"] . "',
			'" . $_POST["nickname"] . "',
			'" . $_POST["title"] . "',
			'" . $_POST["phone"] . "',
			'" . $_POST["email"] . "',
			" . $_POST["departmentID"] . ",
			" . $_POST["corporationID"] . ",
			" . $_POST["officeID"] . ",
			'" . $_POST["bio"] . "',
			GETDATE()
		)");
		
		//prepare email
		reset($_POST);
		$message = "";
		while (list($key, $value) = each($_POST)) {
			$message .= '<tr><td class="left">' . $key . '</td>';
			if ($key == "email") {
				$message .= '<td><a href="mailto:' . $value . '">' . $value . '</a></td></tr>';
			} elseif ($key == "departmentID") {
				$r = db_grab("SELECT departmentName FROM intranet_departments WHERE departmentID = " . $value);
				$message .= '<td>' . $r . '</td></tr>';
			} elseif ($key == "officeID") {
				$r = db_grab("SELECT name FROM intranet_offices WHERE id = " . $value);
				$message .= '<td>' . $r . '</td></tr>';
			} elseif ($key == "corporationID") {
				$message .= '<td>' . db_grab("SELECT description FROM organizations WHERE id = " . $value) . '</td></tr>';
			} elseif ($key == "Additional Info") {
				$message .= '<td>' . nl2br($value) . '</td></tr>';
			} else {
				$message .= '<td>' . $value . '</td></tr>';
			}
		}
		$message .= '<tr><td colspan="2" class="bottom"><a href="http://' . $request["host"] . '/staff/add_edit.php?requestID=' . $id . '">click here</a></td></tr>';

		email_user($_josh["email_admin"], "New User Request", $message, 2);
	}
		
	url_change("account_confirm.php");
}

?>
<html>
	<head>
		<title>Request an Account</title>
		<link rel="stylesheet" type="text/css" href="<?=$locale?>style.css" />
			<script language="javascript" type="text/javascript" src="/javascript.js"></script>
			<script language="javascript" type="text/javascript" src="<?=$locale?>tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
			<script language="javascript">
				<!--
				initTinyMCE("<?=$locale?>style-textarea.css");
				//-->
			</script>
	</head>
	<body>
<br>
<table width="600" align="center">
	<tr>
		<td>
<?
echo drawServerMessage("<h1>Welcome!</h1>  To request an account, please fill out the fields below.  Your login information will be emailed to you once your request is approved.");
$form = new intranet_form;
$form->addRow("itext",			"First Name",	"firstname", '', "", true, 20);
$form->addRow("itext",			"Nickname (optional)", "nickname", '', "", false, 20);
$form->addRow("itext",			"Last Name",	"lastname", '', "", true, 20);
$form->addRow("itext",			"Email",		"email", '', "", true, 50);
$form->addRow("itext",			"Title",		"title", '', "", true, 100);
$form->addRow("select",			"Organization",	"corporationID", "SELECT id, description FROM organizations ORDER BY description", "", true);
$form->addRow("department",		"Department",	"departmentID");
$form->addRow("select",			"Office",		"officeID", "SELECT id, name FROM intranet_offices ORDER BY precedence", "", true);
$form->addRow("phone",			"Phone",		"phone", '', "", true, 14);
$form->addRow("textarea",		"Additional Info", "bio", "", "mceEditor");
$form->addRow("submit",			"Send Request");
$form->draw("Request Intranet Account");
?>
		</td>
	</tr>
</table>
	</body>
</html>