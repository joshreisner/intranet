<?php
include('../include.php');

if ($posting) {

	//debug();
	
	//check to make sure email not already assigned to an active user
	if (!$editing && ($id = db_grab('SELECT id FROM users WHERE is_active = 1 AND email = "' . $_POST['email'] . '"'))) {
		url_change('view.php?id=' . $id);
	}
	
	langTranslatePost('bio,title');
	
	if (!getOption('languages')) $_POST['language_id'] = 1;
	
	if ($uploading) {
		$_POST['image_large'] = format_image_resize(file_get_uploaded('image_large'), 240);
		$_POST['image_medium'] = format_image_resize($_POST['image_large'], 135);
		$_POST['image_small'] = format_image_resize($_POST['image_large'], 50);
	}
	
	$id = db_save('users');
	
	if (getOption('channels')) {
		db_checkboxes('channels', 'users_to_channels', 'user_id', 'channel_id', $id);
		if ((admin() || url_id() == user())) db_checkboxes('email_prefs', 'users_to_channels_prefs', 'user_id', 'channel_id', $id);
	}

	if ($_SESSION['is_admin']) {
		if (isset($_POST['is_admin'])) {
			//is admin, so delete permissions
			db_query('DELETE FROM users_to_modules WHERE user_id = ' . $id);
			db_query('DELETE FROM users_to_modulettes WHERE user_id = ' . $id);
		} else {
			//handle permissions updates
			db_query('UPDATE users SET is_admin = 0 WHERE id = ' . $id);
			db_checkboxes('modules', 'users_to_modules', 'user_id', 'module_id', $id);
			db_checkboxes('modulettes', 'users_to_modulettes', 'user_id', 'modulette_id', $id);
		}
	}
	
	//send invite
	if (!$editing) emailInvite($id);
	
	if (url_id() == user()) {
		//todo, fix this and make it more user-update dependent
		$_SESSION['update_days'] = 0;
		$_SESSION['updated_date'] = 'foo';
	}
	
	//clean up users requests
	if (url_id('requestID')) {
		db_delete('users_requests', $_GET['requestID']);
		error_debug('deleted user request', __file__, __line__);
	}
	
	url_change('view.php?id=' . $id);
} elseif (url_id('requestID')) {
	$values = db_grab('SELECT * FROM users_requests WHERE id = ' . $_GET['requestID']);
} else {
	$values = false;
}

echo drawTop();

$f = new form('users', @$_GET['id'], $page['title']);
$f->set_title_prefix($page['breadcrumbs']);

//public info
$f->set_group(getString('public_info'), increment());
$f->unset_fields(array('image_medium', 'image_small', 'password', 'lastLogin', 'imageID', 'layoutID', 'homepage', 'notify_topics'));
$f->set_field(array('name'=>'firstname', 'type'=>'text', 'label'=>getString('name_first'), 'position'=>increment()));
$f->set_field(array('name'=>'nickname', 'type'=>'text', 'label'=>getString('nickname'), 'position'=>increment()));
$f->set_field(array('name'=>'lastname', 'type'=>'text', 'label'=>getString('name_last'), 'position'=>increment()));
$f->set_field(array('type'=>'select', 'name'=>'organization_id', 'label'=>getString('organization'), 'sql'=>'SELECT id, title' . langExt() . ' title FROM organizations WHERE is_active = 1 ORDER BY precedence', 'required'=>true, 'position'=>increment()));
$f->set_field(array('name'=>'email', 'type'=>'text', 'label'=>getString('email'), 'position'=>increment()));
$f->set_field(array('name'=>'title' . langExt(), 'type'=>'text', 'label'=>getString('staff_title'), 'position'=>increment()));
$f->set_field(array('name'=>'image_large', 'type'=>'file', 'label'=>getString('image'), 'position'=>increment()));
if (getOption('languages')) {
	$f->set_field(array('type'=>'select', 'name'=>'language_id', 'label'=>getString('language'), 'sql'=>'SELECT id, title FROM languages ORDER BY title', 'required'=>true, 'position'=>increment()));
} else {
	$f->set_hidden('language_id', 1);
}
if (getOption('staff_showdept')) {
	$f->set_field(array('type'=>'select', 'name'=>'departmentID', 'label'=>getString('department'), 'sql'=>'SELECT departmentID, departmentName FROM departments WHERE is_active = 1 ORDER BY precedence', 'position'=>increment()));
} else {
	$f->unset_fields('departmentID');
}
if (getOption('staff_showoffice')) {
	$f->set_field(array('type'=>'select', 'name'=>'officeID', 'label'=>getString('location'), 'sql'=>'SELECT id, name FROM offices ORDER BY precedence', 'required'=>true, 'position'=>increment()));
} else {
	$f->unset_fields('officeID');
}
$f->set_field(array('name'=>'bio' . langExt(), 'label'=>getString('bio'), 'type'=>'textarea', 'class'=>'tinymce', 'position'=>increment()));
$f->set_field(array('name'=>'phone', 'label'=>getString('telephone'), 'type'=>'text', 'position'=>increment()));
$f->set_field(array('name'=>'extension', 'label'=>getString('telephone_extension'), 'type'=>'text', 'class'=>'short', 'position'=>increment()));

//communications preferences (user and true admin only)
if (getOption('channels') && (admin() || (url_id() == user()))) {
	$f->set_group(getString('email_prefs'), increment());
	$f->set_field(array('name'=>'email_prefs', 'option_title'=>'title' . langExt(), 'type'=>'checkboxes', 'label'=>getString('email_prefs_label'), 'options_table'=>'channels', 'linking_table'=>'users_to_channels_prefs', 'object_id'=>'user_id', 'option_id'=>'channel_id', 'default'=>'all', 'position'=>increment()));
}

//private information (user and admin only)
if ((getOption('staff_showemergency') || getOption('staff_showhome')) && ($page['is_admin'] || (url_id() == user()))) {

	//home info
	if (getOption('staff_showhome')) {
		$f->set_group(getString('staff_home_info'), increment());
		$f->set_field(array('name'=>'homeAddress1', 'label'=>getString('homeAddress1'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'homeAddress2', 'label'=>getString('homeAddress2'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'homeCity', 'label'=>getString('city'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'homeStateID', 'label'=>getString('state'), 'type'=>'select', 'sql'=>'SELECT stateID, stateName FROM intranet_us_states ORDER BY stateName', 'position'=>increment()));
		$f->set_field(array('name'=>'homeZIP', 'label'=>getString('zip'), 'type'=>'int', 'class'=>'zip', 'position'=>increment()));
		$f->set_field(array('name'=>'homePhone', 'label'=>getString('telephone'), 'type'=>'text', 'class'=>'telephone', 'position'=>increment()));
		$f->set_field(array('name'=>'homeCell', 'label'=>getString('mobile_phone'), 'type'=>'text', 'class'=>'telephone', 'position'=>increment()));
		$f->set_field(array('name'=>'homeEmail', 'label'=>getString('email'), 'type'=>'text', 'class'=>'email', 'position'=>increment()));
	} else {
		$f->unset_fields('homeAddress1,homeAddress2,homeCity,homeCity,homeZIP,homePhone,homeCell,homeEmail');
	}
	
	//emergency info
	if (getOption('staff_showemergency')) {
		$f->set_group(getString('emergency_contact_1'), increment());
		$f->set_field(array('name'=>'emerCont1Name', 'label'=>getString('name'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont1Relationship', 'label'=>getString('relationship'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont1Phone', 'label'=>getString('telephone'), 'type'=>'text', 'class'=>'telephone', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont1Cell', 'label'=>getString('mobile_phone'), 'type'=>'text', 'class'=>'telephone', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont1Email', 'label'=>getString('email'), 'type'=>'text', 'class'=>'email', 'position'=>increment()));

		$f->set_group(getString('emergency_contact_2'), increment());
		$f->set_field(array('name'=>'emerCont2Name', 'label'=>getString('name'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont2Relationship', 'label'=>getString('relationship'), 'type'=>'text', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont2Phone', 'label'=>getString('telephone'), 'type'=>'text', 'class'=>'telephone', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont2Cell', 'label'=>getString('mobile_phone'), 'type'=>'text', 'class'=>'telephone', 'position'=>increment()));
		$f->set_field(array('name'=>'emerCont2Email', 'label'=>getString('email'), 'type'=>'text', 'class'=>'email', 'position'=>increment()));

	} else {
		$f->unset_fields('emerCont1Name,emerCont1Relationship,emerCont1Phone,emerCont1Cell,emerCont1Email,emerCont2Name,emerCont2Relationship,emerCont2Phone,emerCont2Cell,emerCont2Email');
	}
}

//permissions (true admin only)
if (admin()) {
	$f->set_group(getString('permissions'), increment());
	//new rule: only admins can edit permissions
	$f->set_field(array('type'=>'checkbox', 'name'=>'is_admin', 'label'=>getString('is_admin'), 'position'=>increment()));
	$f->set_field(array('type'=>'checkboxes', 'name'=>'modules', 'label'=>getString('module_permissions'), 'options_table'=>'modules', 'linking_table'=>'users_to_modules', 'option_title'=>'title' . langExt(), 'option_id'=>'module_id', 'object_id'=>'user_id', 'position'=>increment()));
	$f->set_field(array('type'=>'checkboxes', 'name'=>'modulettes', 'label'=>getString('modulette_permissions'), 'options_table'=>'modulettes', 'linking_table'=>'users_to_modulettes', 'option_title'=>'title' . langExt(), 'option_id'=>'modulette_id', 'object_id'=>'user_id', 'position'=>increment()));
} else {
	$f->unset_fields('is_admin');
}

//administrative info (admin)
if ($page['is_admin']) {
	$f->set_group(getString('administrative_info'), increment());
	formAddChannels($f, 'users', 'user_id');
	$f->set_field(array('name'=>'startDate', 'label'=>getString('start_date'), 'type'=>'date', 'required'=>true, 'position'=>increment()));
	$f->set_field(array('name'=>'endDate', 'label'=>getString('end_date'), 'type'=>'date', 'required'=>false, 'position'=>increment()));
	
	if (getOption('staff_showrank')) {
		$f->set_field(array('name'=>'rankID', 'label'=>getString('rank'), 'type'=>'select', 'sql'=>'SELECT id, description FROM intranet_ranks ORDER BY sequence', 'default'=>db_grab('SELECT id FROM intranet_ranks WHERE isPayroll = 1'), 'required'=>true, 'position'=>increment()));
	} else {
		$f->unset_fields('rankID');
	}
	
	if (getOption('staff_ldcode')) {
		$f->set_field(array('name'=>'longDistanceCode', 'label'=>getString('ldcode'), 'type'=>'text', 'position'=>increment()));
	} else {
		$f->unset_fields('longDistanceCode');
	}
} else {
	$f->unset_fields('startDate,endDate');
}

$f->unset_fields('isPayroll,isImagePublic,help');

langUnsetFields($f, 'title,bio');
langTranslateCheckbox($f, url_id());

echo $f->draw($values);

echo drawBottom();
?>