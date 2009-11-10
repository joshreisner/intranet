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

echo drawTopSimple(getString('password_reset'));

if (@$_GET["msg"] == 'email-not-found') {
	echo drawMessage(getString('login_password_reset_msg_email_not_found'));
} else {
	echo drawMessage(getString('login_password_reset_msg'));
}

$form = new form('login', false, getString('password_reset'));
$form->set_field(array('name'=>'email', 'label'=>getString('email'), 'type'=>'text', 'value'=>@$_GET["email"]));
echo $form->draw();

echo drawBottomSimple();
?>