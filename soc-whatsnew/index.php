<?php
include('../include.php');

if (url_action('delete')) {
	db_delete('soc_whatsnew');
	url_drop('action,id');
}

echo drawTop();

$t = new table('soc_whatsnew', 'What\'s New<a class="right s" href="edit/">add new</a>');
$t->set_column('draggy', 'd', '&nbsp;');
$t->set_column('title');
$t->set_column('updated', 'r');
$t->set_column('delete', 'd', '&nbsp;');
$t->set_draggable('/ajax/reorder.php', 'draggy');

$result = db_table('SELECT w.id, w.title, ' . db_updated('w') . ' FROM soc_whatsnew w WHERE w.is_active = 1 ORDER BY w.precedence');

foreach ($result as &$r) {
	$r['draggy']	= '<img src="../images/icons/move.png" alt="move" width="16" height="16"/>';
	$r['title']		= draw_link('edit/?id=' . $r['id'], format_string($r['title'], 70));
	$r['updated']	= format_date($r['updated']);
	$r['delete']	= deleteColumn($r['id']);
}

echo $t->draw($result);

echo drawBottom();
?>