<?php
$pageIsPublic = true;
include("../include.php");

if ($posting) {

	$_POST["email"] = format_email($_POST["email"]);
	$_POST["phone"] = format_phone($_POST["phone"]);
	format_post_nulls("departmentID, officeID");
	
	//create request
	//todo ~ check whether staff already exists -- forward to password reset
	if ($id = db_grab("SELECT id FROM users WHERE email = '" . $_POST["email"] . "' AND is_active = 1")) {
		url_change("account_exists.php");
	} elseif ($id = db_grab("SELECT id FROM users_requests WHERE email = '" . $_POST["email"] . "'")) {
		db_query("UPDATE users_requests SET
			firstname = '" . $_POST["firstname"] . "', 
			lastname = '" . $_POST["lastname"] . "',
			nickname = '" . $_POST["nickname"] . "',
			title = '" . $_POST["title"] . "',
			phone = '" . $_POST["phone"] . "',
			email = '" . $_POST["email"] . "',
			departmentID = " . $_POST["departmentID"] . ",
			organization_id = '" . $_POST["organization_id"] . "',
			officeID = " . $_POST["officeID"] . ",
			bio = '" . $_POST["bio"] . "',
			created_date = GETDATE()
		WHERE email = '" . $_POST["email"] . "'");
		emailAdmins(drawEmptyResult($_POST["firstname"] . " " . $_POST["lastname"] . ' is <a href="http://' . $request["host"] . '/staff/add_edit.php?requestID=' . $id . '">re-requesting an account</a>.', "Repeat Account Request"));
	} else {
		$id = db_query("INSERT INTO users_requests (
			firstname, 
			lastname,
			nickname,
			title,
			phone,
			email,
			departmentID,
			organization_id,
			officeID,
			bio,
			created_date
		) VALUES (
			'" . $_POST["firstname"] . "', 
			'" . $_POST["lastname"] . "',
			'" . $_POST["nickname"] . "',
			'" . $_POST["title"] . "',
			'" . $_POST["phone"] . "',
			'" . $_POST["email"] . "',
			" . $_POST["departmentID"] . ",
			" . $_POST["organization_id"] . ",
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
				$r = db_grab("SELECT departmentName FROM departments WHERE departmentID = " . $value);
				$message .= '<td>' . $r . '</td></tr>';
			} elseif ($key == "officeID") {
				$r = db_grab("SELECT name FROM offices WHERE id = " . $value);
				$message .= '<td>' . $r . '</td></tr>';
			} elseif ($key == "organization_id") {
				$message .= '<td>' . db_grab("SELECT title from organizations WHERE id = " . $value) . '</td></tr>';
			} elseif ($key == "Additional Info") {
				$message .= '<td>' . nl2br($value) . '</td></tr>';
			} else {
				$message .= '<td>' . $value . '</td></tr>';
			}
		}
		$message .= '<tr><td colspan="2" class="bottom"><a href="http://' . $request["host"] . '/staff/add_edit.php?requestID=' . $id . '">click here</a></td></tr>';

		emailAdmins($message, "New User Request", 2);
	}
		
	url_change("account_confirm.php");
}

?>
<html>
	<head>
		<title>Request an Account</title>
		<link rel="stylesheet" type="text/css" href="/styles/screen.css" />
			<script language="javascript" type="text/javascript" src="/javascript.js"></script>
			<script language="javascript" type="text/javascript" src="<?=$_josh["write_folder"]?>/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
			<script language="javascript">
				<!--
				initTinyMCE("<?=$_josh["write_folder"]?>/tinymce.css");
				//-->
			</script>
	</head>
	<body>
<br>
<table width="600" align="center">
	<tr>
		<td>
<?
echo drawMessage("<h1>Welcome!</h1>  To request an account, please fill out the fields below.  Your login information will be emailed to you once your request is approved.");
$form = new intranet_form;
$form->addRow("itext",			"First Name",	"firstname", '', "", true, 20);
$form->addRow("itext",			"Nickname (optional)", "nickname", '', "", false, 20);
$form->addRow("itext",			"Last Name",	"lastname", '', "", true, 20);
$form->addRow("itext",			"Email",		"email", '', "", true, 50);
$form->addRow("itext",			"Title",		"title", '', "", true, 100);
$form->addRow("select",			"Organization",	"organization_id", "SELECT id, title from organizations ORDER BY title", "", false);
$form->addJavascript("!form.organization_id.value.length", "the 'Organization' field is not selected");
if (getOption("staff_showdept")) {
	$form->addRow("department",		"Department",	"departmentID", "", "", false);
	$form->addJavascript("!form.departmentID.value.length", "the 'Department' field is not selected");
}
if (getOption("staff_showoffice")) {
	$form->addRow("select",			"Office",		"officeID", "SELECT id, name FROM offices ORDER BY precedence", "", false);
	$form->addJavascript("!form.officeID.value.length", "the 'Office' field is not selected");
}
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