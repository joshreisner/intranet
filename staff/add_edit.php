<?php
include('../include.php');

echo drawTop();

$f = new form('users', @$_GET['id'], $page['title']);
$f->set_title_prefix($page['breadcrumbs']);
$offset = 0;

//public info
$f->set_group('Public Information', 0);
$f->unset_fields(array('image_medium', 'image_small', 'password', 'lastLogin'));
$f->set_field_labels(array('firstname'=>'First Name', 'lastname'=>'Last Name', 'image_large'=>'Image'));
$f->set_field(array('type'=>'select', 'name'=>'organization_id', 'sql'=>'SELECT id, title FROM organizations WHERE is_active = 1 ORDER BY precedence'));
if (getOption("staff_showdept")) {
	$f->set_field(array('type'=>'select', 'name'=>'departmentID', 'sql'=>'SELECT departmentID, departmentName FROM departments WHERE is_active = 1 ORDER BY precedence'));
	$offset++;
}
if (getOption("staff_showoffice")) {
	$f->set_field(array('type'=>'select', 'name'=>'officeID', 'sql'=>'SELECT id, name FROM offices ORDER BY name'));
	$offset++;
}

//administrative info
if ($page['is_admin']) {
	$f->set_group('Administrative Information', 10 + $offset);
	if (getOption('channels')) {
		$f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>getString('networks'), 'options_table'=>'channels', 'linking_table'=>'users_to_channels', 'object_id'=>'user_id', 'option_id'=>'channel_id', 'default'=>'all', 'position'=>11 + $offset));
		$offset++;
	}
	$f->set_field(array('type'=>'checkboxes', 'name'=>'permissions', 'options_table'=>'modules', 'linking_table'=>'users_to_modules', 'option_id'=>'module_id', 'object_id'=>'user_id', 'position'=>11 + $offset));
}

//home info
if (getOption("staff_showhome")) {
}

//emergency info
if (getOption("staff_showemergency")) {
}


echo $f->draw();

echo drawBottom();
?>