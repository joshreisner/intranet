<?php
include("../../include.php");

if ($posting) {
	langTranslatePost('title');
	$id = db_save('channels');
	url_drop('id');
} elseif (url_action('delete') && url_id('delete_id')) {
	db_delete('channels', $_GET['delete_id']);
	url_drop('delete_id,action');
}

echo drawTop();

if (url_id()) {
	//form
	$f = new form('channels', @$_GET['id']);
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f);
	echo $f->draw();
} else {
	//modules list
	$t = new table('channels', drawHeader());
	$t->set_column('draggy', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('delete', 'd', '&nbsp;');
	
	$result = db_table('SELECT id, title' . langExt() . ' title FROM channels WHERE is_active = 1 ORDER BY precedence');
	$t->set_draggable('draggy');
	
	foreach ($result as &$r) {
		$r['draggy']		= draw_img('/images/icons/move.png');
		$r['title']			= draw_link('channels.php?id=' . $r['id'], $r['title']);
		$r['delete']	= drawColumnDelete($r['id']);
	}
	
	echo $t->draw($result, 'No modules');
	
	//add new
	$f = new form('channels');
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f, false);
	echo $f->draw();
}

echo drawBottom();
?>