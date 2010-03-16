<?php
include("../../include.php");

if ($posting) {
	langTranslatePost('title');
	$id = db_save('modules');
	url_drop('id');
}

echo drawTop();

if (url_id()) {
	//form
	$f = new form('modules', @$_GET['id']);
	$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
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
	
	$result = db_table('SELECT m.id, m.title' . langExt() . ' title, m.is_active, (SELECT COUNT(*) FROM pages p WHERE p.module_id = m.id AND p.modulette_id IS NULL) pages FROM modules m ORDER BY m.precedence');
	$t->set_draggable('draggy');
	
	foreach ($result as &$r) {
		$r['is_selected']	= draw_form_checkbox('foo', $r['is_active'], false, 'ajax_set(\'modules\', \'is_active\', ' . $r['id'] . ', ' . abs($r['is_active'] - 1) . ');');
		$r['draggy']		= draw_img('/images/icons/move.png');
		$r['title']			= draw_link('./?id=' . $r['id'], $r['title']);
		$r['pages']			= draw_link('pages.php?module_id=' . $r['id'], format_quantitize($r['pages'], 'page'));
	}
	
	echo $t->draw($result, 'No modules');
}

echo drawBottom();
?>