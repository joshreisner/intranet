<?php
include("../../include.php");

if ($posting) {
	langTranslatePost('title');
	$id = db_save('links');
	url_drop('id');
} elseif (url_action('delete') && url_id('delete_id')) {
	db_delete('links', $_GET['delete_id']);
	url_drop('delete_id,action');
}

echo drawTop();

if (url_id()) {
	//form
	$f = new form('links', @$_GET['id']);
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f);
	echo $f->draw();
} else {
	//modules list
	$t = new table('links', drawHeader());
	$t->set_column('draggy', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('delete', 'd', '&nbsp;');
	
	$result = db_table('SELECT l.id, l.title' . langExt() . ' title FROM links l WHERE l.is_active = 1 ORDER BY l.precedence');
	$t->set_draggable('draggy');
	
	foreach ($result as &$r) {
		$r['draggy']		= draw_img('/images/icons/move.png');
		$r['title']			= draw_link('links.php?id=' . $r['id'], $r['title']);
		$r['delete']	= deleteColumn($r['id']);
	}
	
	echo $t->draw($result, 'No modules');
	
	//add new
	$f = new form('links');
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f, false);
	echo $f->draw();
}

echo drawBottom();
?>