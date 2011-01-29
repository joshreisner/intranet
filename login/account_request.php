<?php
$pageIsPublic = true;
include('../include.php');

if ($posting) {
	$_POST['email'] = format_email($_POST['email']);
	$_POST['phone'] = format_phone($_POST['phone']);
	format_post_nulls('departmentID, officeID');
	
	//create request
	//todo ~ check whether staff already exists -- forward to password reset
	if ($id = db_grab('SELECT id FROM users WHERE email = "' . $_POST['email'] . '" AND is_active = 1')) {
		url_change('account_exists.php');
	} elseif (getOption('requests') && ($id = db_grab('SELECT id FROM users_requests WHERE email = "' . $_POST['email'] . '"'))) {
		db_save('users_requests', $id);
		emailAdmins('Repeat Account Request', $_POST['firstname'] . ' ' . $_POST['lastname'] . ' is ' . draw_link(url_base() . '/staff/add_edit.php?requestID=' . $id, 're-requesting an account'));
	} else {
		if (getOption('requests')) {
			$id = db_save('users_requests');
			//if (getOption('channels')) db_checkboxes('email_prefs', 'requests_to_channels_prefs', 'request_id', 'channel_id', $id);
			$subject = 'New User Request';
			$link = url_base() . '/staff/add_edit.php?requestID=' . $id;
		} else {
			$id = db_save('users');
			if (getOption('channels')) db_checkboxes('email_prefs', 'users_to_channels_prefs', 'user_id', 'channel_id', $id);
			$subject = 'New User Registration';
			$link = url_base() . '/staff/add_edit.php?id=' . $id;
			emailInvite($id);
		}
		
		//prepare email
		$message = '';
		reset($_POST);
		while (list($key, $value) = each($_POST)) {
			if ($key == 'email') {
				$value = draw_link('mailto:' . $value);
			} elseif (($key == 'departmentID') && $value) {
				$value = db_grab('SELECT departmentName FROM departments WHERE departmentID = ' . $value);
			} elseif (($key == 'officeID') && $value) {
				$value = db_grab('SELECT name FROM offices WHERE id = ' . $value);
			} elseif (($key == 'organization_id') && $value) {
				$value = db_grab('SELECT title from organizations WHERE id = ' . $value);
			} elseif ($key == 'Additional Info') {
				$value = nl2br($value);
			}
			$message .= '<tr><td class="left">' . $key . '</td><td>' . $value . '</td></tr>';
		}
		$message .= '<tr><td colspan="2" class="bottom">' . draw_link($link, 'click here') . '</td></tr>';
		$message = '<table border="1">' . $message . '</table>';
		emailAdmins($message, $subject);
	}
		
	url_change('account_confirm.php');
}

echo drawSimpleTop(getString('login_account_request'));

echo drawMessage(getString('login_account_request_msg'));
$f = new form('users_requests', false, getString('login_account_request'));
$f->set_field(array('type'=>'select', 'sql'=>'SELECT id, title' . langExt() . ' title FROM organizations WHERE is_active = 1 ORDER BY precedence', 'name'=>'organization_id', 'label'=>getString('organization'), 'required'=>true, 'null_value'=>getString('please_select')));
$f->set_field(array('type'=>'text', 'name'=>'firstname', 'label'=>getString('name_first')));
$f->set_field(array('type'=>'text', 'name'=>'nickname', 'label'=>getString('nickname')));
$f->set_field(array('type'=>'text', 'name'=>'lastname', 'label'=>getString('name_last')));
$f->set_field(array('type'=>'text', 'name'=>'title', 'label'=>getString('staff_title')));
$f->set_field(array('type'=>'text', 'name'=>'phone', 'label'=>getString('telephone')));
$f->set_field(array('type'=>'text', 'name'=>'email', 'label'=>getString('email')));
if (getOption('staff_showoffice')) {
	$f->set_field(array('type'=>'select', 'name'=>'officeID', 'label'=>getString('location'), 'sql'=>'SELECT id, name FROM offices ORDER BY precedence', 'required'=>true));
} else {
	$f->unset_fields('officeID');
}
if (getOption('staff_showdept')) {
	$f->set_field(array('type'=>'select', 'name'=>'departmentID', 'label'=>getString('department'), 'sql'=>'SELECT departmentID, departmentName FROM departments WHERE is_active = 1 ORDER BY precedence'));
} else {
	$f->unset_fields('departmentID');
}
if (getOption('channels') && (url_id() == user())) {
	$f->set_group(getString('email_prefs'));
	$f->set_field(array('name'=>'email_prefs', 'option_title'=>'title' . langExt(), 'type'=>'checkboxes', 'label'=>getString('email_prefs_label'), 'options_table'=>'channels', 'linking_table'=>'users_to_channels_prefs', 'object_id'=>'user_id', 'option_id'=>'channel_id', 'default'=>'all'));
}
if (getOption('legal')) {
	$f->set_field(array('type'=>'checkbox', 'name'=>'legal', 'label'=>getString('legal_checkbox')));
}
$f->set_field(array('type'=>'textarea', 'name'=>'bio', 'label'=>getString('bio'), 'class'=>'tinymce'));

echo $f->draw();
echo drawSimpleBottom();
?>