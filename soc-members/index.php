<?php
include('../include.php');

if (url_action('delete')) {
	db_delete('jr_members_inst');
	url_drop('action,id');
}

echo drawTop();

$t = new table('jr_members_inst', 'Institutional Members');
$t->set_column('is_selected', 'd', '&nbsp;');
$t->set_column('name');
$t->set_column('updated', 'r');
$t->set_column('delete', 'd', '&nbsp;');

$result = db_table('SELECT m.id, m.name, m.is_selected, c.en "group", ' . db_updated('m') . ' FROM jr_members_inst m JOIN jr_countries c ON m.country = c.id WHERE m.is_active = 1 ORDER BY c.en, m.name');

foreach ($result as &$r) {
	$r['is_selected']	= draw_form_checkbox('foo', $r['is_selected'], false, 'ajax_set(\'jr_members_inst\', \'is_selected\', ' . $r['id'] . ', ' . abs($r['is_selected'] - 1) . ');');
	$r['name']		= draw_link('edit/?id=' . $r['id'], $r['name']);
	$r['updated']	= format_date($r['updated']);
	$r['delete']	= deleteColumn($r['id']);
}

echo $t->draw($result);

echo drawBottom();
?>