<?php
include("../include.php");

if ($posting) {
	langTranslatePost('title');	
	$id = db_save('modules');
}

drawTop();

if (url_id()) {
	//form
	$f = new form('modules', @$_GET['id']);
	$f->unset_fields('isPublic, hasChildren, pallet');
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f);
	echo $f->draw();
} else {
	//modules list
	$t = new table('modules', drawHeader());
	$t->set_column('is_selected', 'd', '&nbsp;');
	$t->set_column('draggy', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('pages', 'r');
	
	$result = db_table('SELECT m.id, m.title, m.is_active, (SELECT COUNT(*) FROM pages p WHERE p.module_id = m.id) pages FROM modules m ORDER BY m.precedence');
	$t->set_draggable('draggy');
	
	foreach ($result as &$r) {
		$r['is_selected']	= draw_form_checkbox('foo', $r['is_active'], false, 'ajax_set(\'modules\', \'is_active\', ' . $r['id'] . ', ' . abs($r['is_active'] - 1) . ');');
		$r['draggy']		= draw_img('/images/icons/move.png');
		$r['title']			= draw_link('./?id=' . $r['id'], $r['title']);
		$r['pages']			= format_q($r['pages'], 'page');
	}
	
	echo $t->draw($result, 'No modules');
}




drawBottom();
?>