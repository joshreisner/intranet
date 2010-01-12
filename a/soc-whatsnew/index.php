<?php
include('../../include.php');

if (url_action('delete')) {
	db_delete('soc_whatsnew', $_GET['delete_id']);
	url_drop('action,delete_id');
}

echo drawTop();

$t = new table('soc_whatsnew', drawHeader(array('edit/'=>getString('add_new'))));
$t->set_column('draggy', 'd', '&nbsp;');
$t->set_column('title', 'l', getString('title'));
$t->set_column('updated', 'r', getString('updated'));
$t->set_column('delete', 'd', '&nbsp;');

$result = db_table('SELECT w.id, w.title' . langExt() . ' title, ' . db_updated('w') . ' FROM soc_whatsnew w WHERE w.is_active = 1 ORDER BY w.precedence');

foreach ($result as &$r) {
	$r['draggy']	= draw_img('/images/icons/move.png');
	$r['title']		= draw_link('edit.php?id=' . $r['id'], format_string($r['title'], 70));
	$r['updated']	= format_date($r['updated']);
	$r['delete']	= deleteColumn($r['id']);
}

echo $t->draw($result);

echo drawBottom();
?>