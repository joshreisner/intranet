<?
$included = !@include('../../include.php');

if ($posting) {
	$id = db_save('external_orgs');
	db_checkboxes('types', 'external_orgs_to_types', 'org_id', 'type_id', $id);
	if (getOption('channels')) db_checkboxes('channels', 'external_orgs_to_channels', 'org_id', 'channel_id', $id);
	url_change_post('/' . $location . '/type.php?id=' . db_grab('SELECT type_id FROM external_orgs_to_types WHERE org_id = ' . $id)); //pure hackery
} elseif ($included) {
	$_josh['referrer'] = false;
	$_josh['request']['path_query'] = '/' . $location . '/edit/'; //shoddy way of setting the form target
	$r['url'] = 'http://';
} else {
	url_query_require();
	drawTop();
	$r = db_grab('SELECT id, title, url, description from external_orgs WHERE id = ' . $_GET['id']);
}

if ($included) $_GET['id'] = false; //type id was auto-setting checkbox

$f = new form('external_orgs', @$_GET['id'], $page['name']);
if (!$included) $f->set_title_prefix(drawHeader(false, ' '));
$f->set_field(array('name'=>'types', 'type'=>'checkboxes', 'options_table'=>'external_orgs_types', 'linking_table'=>'external_orgs_to_types', 'object_id'=>'org_id', 'option_id'=>'type_id'));
if (getOption('channels')) $f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>'Networks', 'options_table'=>'channels', 'linking_table'=>'external_orgs_to_channels', 'object_id'=>'org_id', 'option_id'=>'channel_id'));
echo $f->draw($r, !$included);

/*
$form = new intranet_form;
$form->addRow('itext',  'Name', 'title', @$r['title'], '', true, 255);
$form->addCheckboxes('types', 'Types', 'external_orgs_types', 'external_orgs_to_types', 'org_id', 'type_id', @$_GET['id']);
$form->addJavascript('form_checkboxes_empty(form, 'types')', 'a 'Type' must be checked');
$form->addRow('itext',  'URL', 'url', @$r['url'], '', true, 255);
$form->addJavascript('form.url.value == 'http://'', 'the 'URL' field is empty');
$form->addRow('textarea', 'Description', 'description', @$r['description'], '', true);
if ($included) {
	//we are on the index page
	$form->addRow('submit', 'Add Org');
	$form->draw('Add New Organization');
} else {
	//we are on this here page
	$form->addRow('submit', 'Save Changes');
	$form->draw('Edit Organization');
}
*/

if (!$included) drawBottom();
?>