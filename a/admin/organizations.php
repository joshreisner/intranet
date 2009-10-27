<?php
include("../../include.php");

if ($posting) {
	$id = db_save('organizations');
	url_drop('id');
} elseif (url_action('delete') && url_id()) {
	db_delete('organizations');
	url_drop('id,action');
}

echo drawTop();

if (url_id()) {
	//form
	$f = new form('organizations', @$_GET['id']);
	echo $f->draw();
} else {
	//modules list
	$t = new table('organizations', drawHeader());
	$t->set_column('draggy', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('delete', 'd', '&nbsp;');
	
	$result = db_table('SELECT id, title FROM organizations WHERE is_active = 1 ORDER BY precedence');
	$t->set_draggable('draggy');
	
	foreach ($result as &$r) {
		$r['draggy']		= draw_img('/images/icons/move.png');
		$r['title']			= draw_link('organizations.php?id=' . $r['id'], $r['title']);
		$r['delete']	= deleteColumn($r['id']);
	}
	
	echo $t->draw($result, 'No modules');
	
	//add new
	$f = new form('organizations');
	echo $f->draw();
}

echo drawBottom();
?>