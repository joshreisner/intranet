<?php
$included = isset($_josh);

if (!$included) include('../include.php');

$r = array();

if ($posting) {
	langTranslatePost('title,description');
	$id = db_save('external_orgs');
	db_checkboxes('types', 'external_orgs_to_types', 'org_id', 'type_id', $id);
	if (getOption('channels')) db_checkboxes('channels', 'external_orgs_to_channels', 'org_id', 'channel_id', $id);
	url_change_post('./type.php?id=' . db_grab('SELECT type_id FROM external_orgs_to_types WHERE org_id = ' . $id)); //pure hackery
} elseif ($included) {
	$title = getString('add_new');
	$_josh['referrer'] = false;
	$_josh['request']['path_query'] = 'edit.php'; //shoddy way of setting the form target
	$r['url'] = 'http://';
} else {
	url_query_require();
	echo drawTop();
	$title = $page['title'];
	$r = db_grab('SELECT id, title, url, description from external_orgs WHERE id = ' . $_GET['id']);
}

if ($included) $_GET['id'] = false; //type id was auto-setting checkbox

echo '<a name="bottom"></a>';

$f = new form('external_orgs', @$_GET['id'], $title);
if (!$included) $f->set_title_prefix(drawHeader(false, ' '));
$f->set_field(array('name'=>'title' . langExt(), 'type'=>'text', 'label'=>getString('title')));
$f->set_field(array('name'=>'description' . langExt(), 'type'=>'textarea', 'label'=>getString('description'), 'class'=>'tinymce'));
$f->set_field(array('name'=>'url' . langExt(), 'type'=>'text', 'label'=>getString('url')));
$f->set_field(array('name'=>'types', 'label'=>getString('type'), 'option_title'=>'title' . langExt(), 'type'=>'checkboxes', 'options_table'=>'external_orgs_types', 'linking_table'=>'external_orgs_to_types', 'object_id'=>'org_id', 'option_id'=>'type_id'));
if (getOption('channels')) $f->set_field(array('name'=>'channels', 'label'=>getString('networks'), 'option_title'=>'title' . langExt(), 'type'=>'checkboxes', 'options_table'=>'channels', 'linking_table'=>'external_orgs_to_channels', 'object_id'=>'org_id', 'option_id'=>'channel_id'));
langUnsetFields($f, 'title,description');
langTranslateCheckbox($f, url_id());
echo $f->draw($r, !$included);

if (!$included) echo drawBottom();
?>