<?php
include("../../include.php");

if ($posting) {
	langTranslatePost('title');
	$id = db_save('organizations');
	url_drop('id');
} elseif (url_action('delete') && url_id('delete_id')) {
	db_delete('organizations', $_GET['delete_id']);
	url_drop('delete_id,action');
}

echo drawTop();

if (url_id()) {
	//form
	$f = new form('organizations', @$_GET['id']);
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f);
	echo $f->draw();
} else {
	//modules list
	$t = new table('organizations', drawHeader());
	$t->set_column('draggy', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('delete', 'd', '&nbsp;');
	
	$result = db_table('SELECT id, title' . langExt() . ' title FROM organizations WHERE is_active = 1 ORDER BY precedence');
	
	foreach ($result as &$r) {
		$r['draggy'] = draw_img('/images/icons/move.png');
		$r['title'] = draw_link('organizations.php?id=' . $r['id'], $r['title']);
		$r['delete'] = drawColumnDelete($r['id']);
	}
	
	echo $t->draw($result, 'No organizations');
	
	//add new
	$f = new form('organizations');
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
	langUnsetFields($f, 'title');
	echo $f->draw(false, false);
}

echo drawBottom();
?>