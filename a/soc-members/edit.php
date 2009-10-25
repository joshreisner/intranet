<?php
include('../../include.php');

if ($posting) {
	format_post_bits('is_selected');
	langTranslatePost('name,description');
	$id = db_save('soc_members');
	url_change_post('../');
}

echo drawTop();

$f = new form('soc_members', @$_GET['id'], $page['title']);
$f->set_field(array('name'=>'name' . langExt(), 'type'=>'text', 'label'=>getString('title')));
$f->set_field(array('name'=>'country_id', 'type'=>'select', 'sql'=>'SELECT id, en FROM jr_countries ORDER BY en'));
$f->set_field(array('name'=>'description' . langExt(), 'type'=>'textarea', 'class'=>'mceEditor', 'label'=>getString('description')));
langUnsetFields($f, 'name,description');
langTranslateCheckbox($f);
$f->set_title_prefix($page['breadcrumbs']);
echo $f->draw();

echo drawBottom();
?>