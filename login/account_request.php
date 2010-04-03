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
		db_save('users_requests', $id);
		emailAdmins(drawEmptyResult($_POST["firstname"] . " " . $_POST["lastname"] . ' is <a href="' . url_base() . '/staff/add_edit.php?requestID=' . $id . '">re-requesting an account</a>.'), "Repeat Account Request");
	} else {
		$id = db_save('users_requests');
		
		//prepare email
		$message = '';
		while (list($key, $value) = each($_POST)) {
			$message .= '<tr><td class="left">' . $key . '</td>';
			if ($key == 'email') {
				$message .= '<td><a href="mailto:' . $value . '">' . $value . '</a></td></tr>';
			} elseif ($key == 'departmentID') {
				$r = db_grab("SELECT departmentName FROM departments WHERE departmentID = " . $value);
				$message .= '<td>' . $r . '</td></tr>';
			} elseif ($key == 'officeID') {
				$r = db_grab("SELECT name FROM offices WHERE id = " . $value);
				$message .= '<td>' . $r . '</td></tr>';
			} elseif ($key == 'organization_id') {
				$message .= '<td>' . db_grab("SELECT title from organizations WHERE id = " . $value) . '</td></tr>';
			} elseif ($key == 'Additional Info') {
				$message .= '<td>' . nl2br($value) . '</td></tr>';
			} else {
				$message .= '<td>' . $value . '</td></tr>';
			}
		}
		$message .= '<tr><td colspan="2" class="bottom"><a href="' . url_base() . '/staff/add_edit.php?requestID=' . $id . '">click here</a></td></tr>';

		emailAdmins($message, "New User Request", 2);
	}
		
	url_change("account_confirm.php");
}

echo drawTopSimple(getString('login_account_request'));

echo drawMessage(getString('login_account_request_msg'));
$f = new form('users_requests', false, getString('login_account_request'));
$f->set_field(array('type'=>'select', 'sql'=>'SELECT id, title' . langExt() . ' title FROM organizations WHERE is_active = 1 ORDER BY precedence', 'name'=>'organization_id', 'label'=>getString('organization'), 'required'=>true));
$f->set_field(array('type'=>'text', 'name'=>'firstname', 'label'=>getString('name_first')));
$f->set_field(array('type'=>'text', 'name'=>'nickname', 'label'=>getString('nickname')));
$f->set_field(array('type'=>'text', 'name'=>'lastname', 'label'=>getString('name_last')));
$f->set_field(array('type'=>'text', 'name'=>'title', 'label'=>getString('staff_title')));
$f->set_field(array('type'=>'text', 'name'=>'phone', 'label'=>getString('telephone')));
$f->set_field(array('type'=>'text', 'name'=>'email', 'label'=>getString('email')));
if (getOption('legal')) {
	$f->set_field(array('type'=>'checkbox', 'name'=>'legal', 'label'=>getString('legal_checkbox')));
}
$f->set_field(array('type'=>'textarea', 'name'=>'bio', 'label'=>getString('bio')));

echo $f->draw();
echo drawBottomSimple();
?>