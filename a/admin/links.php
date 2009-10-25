<?php
include("../../include.php");

if ($posting) {
	langTranslatePost('title');
	$id = db_save('links');
	url_drop('id');
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
	
	$result = db_table('SELECT l.id, l.title' . langExt() . ' title FROM links l WHERE l.is_active = 1 ORDER BY l.precedence');
	$t->set_draggable('draggy');
	
	foreach ($result as &$r) {
		$r['draggy']		= draw_img('/images/icons/move.png');
		$r['title']			= draw_link('links.php?id=' . $r['id'], $r['title']);
	}
	
	echo $t->draw($result, 'No modules');
}

echo drawBottom();
?>