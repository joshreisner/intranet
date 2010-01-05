<?php
include('../include.php');

function increment() {
	global $_josh;
	if (!isset($_josh['increment'])) $_josh['increment'] = 0;
	$_josh['increment']++;
	return $_josh['increment'];
}

if ($posting) {
	langTranslatePost('bio,title');
	$id = db_save('users');
	if (getOption('channels')) db_checkboxes('channels', 'users_to_channels', 'user_id', 'channel_id', $id);
	if ($_SESSION['is_admin']) {
		if (isset($_POST['is_admin'])) {
			//is admin, so delete permissions
			db_query('DELETE FROM users_to_modules WHERE user_id = ' . $id);
			db_query('DELETE FROM users_to_modulettes WHERE user_id = ' . $id);
		} else {
			
			db_query('UPDATE users SET is_admin = 0 WHERE id = ' . $id);
			db_checkboxes('modules', 'users_to_modules', 'user_id', 'module_id', $id);
			db_checkboxes('modulettes', 'users_to_modulettes', 'user_id', 'modulette_id', $id);
		}
	}
	url_change('view.php?id=' . $id);
}

echo drawTop();

$f = new form('users', @$_GET['id'], $page['title']);
$f->set_title_prefix($page['breadcrumbs']);

//public info
$f->set_group('Public Information', increment());
$f->unset_fields(array('image_medium', 'image_small', 'password', 'lastLogin'));
$f->set_field(array('name'=>'firstname', 'type'=>'text', 'label'=>'First Name', 'position'=>increment()));
$f->set_field(array('name'=>'nickname', 'type'=>'text', 'label'=>'Nickname', 'position'=>increment()));
$f->set_field(array('name'=>'lastname', 'type'=>'text', 'label'=>'Last Name', 'position'=>increment()));
$f->set_field(array('type'=>'select', 'name'=>'organization_id', 'sql'=>'SELECT id, title FROM organizations WHERE is_active = 1 ORDER BY precedence', 'position'=>increment()));
$f->set_field(array('name'=>'email', 'type'=>'text', 'label'=>'Email', 'position'=>increment()));
$f->set_field(array('name'=>'image_large', 'type'=>'file', 'label'=>'Image', 'position'=>increment()));


if (getOption("staff_showdept")) $f->set_field(array('type'=>'select', 'name'=>'departmentID', 'sql'=>'SELECT departmentID, departmentName FROM departments WHERE is_active = 1 ORDER BY precedence', 'position'=>increment()));
if (getOption("staff_showoffice")) $f->set_field(array('type'=>'select', 'name'=>'officeID', 'sql'=>'SELECT id, name FROM offices ORDER BY name', 'position'=>increment()));

$f->set_field(array('name'=>'bio', 'type'=>'textarea', 'class'=>'tinymce', 'position'=>increment()));
$f->set_field(array('name'=>'phone', 'type'=>'text', 'position'=>increment()));
$f->set_field(array('name'=>'extension', 'type'=>'text', 'class'=>'short', 'position'=>increment()));

//administrative info
if ($_SESSION['is_admin']) {
	$f->set_group('Permissions', increment());
	//new rule: only admins can edit permissions
	$f->set_field(array('type'=>'checkbox', 'name'=>'is_admin', 'position'=>increment()));
	$f->set_field(array('type'=>'checkboxes', 'name'=>'modules', 'label'=>'Module Permissions', 'options_table'=>'modules', 'linking_table'=>'users_to_modules', 'option_id'=>'module_id', 'object_id'=>'user_id', 'position'=>increment()));
	$f->set_field(array('type'=>'checkboxes', 'name'=>'modulettes', 'label'=>'Modulette Permissions', 'options_table'=>'modulettes', 'linking_table'=>'users_to_modulettes', 'option_id'=>'modulette_id', 'object_id'=>'user_id', 'position'=>increment()));
}


//administrative info
if ($page['is_admin']) {
	$f->set_group('Administrative Information', increment());
	if (getOption('channels')) $f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>getString('networks'), 'options_table'=>'channels', 'linking_table'=>'users_to_channels', 'object_id'=>'user_id', 'option_id'=>'channel_id', 'default'=>'all', 'position'=>increment()));
	$f->set_field(array('name'=>'startDate', 'label'=>'Start Date', 'type'=>'date', 'required'=>true, 'position'=>increment()));
	$f->set_field(array('name'=>'endDate', 'label'=>'End Date', 'type'=>'date', 'required'=>false, 'position'=>increment()));
} else {
	$f->unset_fields('startDate,endDate');
}

//home info
if (getOption("staff_showhome")) {
} else {
	$f->unset_fields('homeAddress1,homeAddress2,homeCity,homeCity,homeZIP,homePhone,homeCell,homeEmail');
}

//emergency info
if (getOption("staff_showemergency")) {
} else {
	$f->unset_fields('emerCont1Name,emerCont1Relationship,emerCont1Phone,emerCont1Cell,emerCont1Email,emerCont2Name,emerCont2Relationship,emerCont2Phone,emerCont2Cell,emerCont2Email');
}

$f->unset_fields('isPayroll,isImagePublic,help');

echo $f->draw();

echo drawBottom();
?>