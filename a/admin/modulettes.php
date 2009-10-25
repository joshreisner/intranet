<?php
include("../../include.php");

if ($posting) {
	langTranslatePost('title');	
	$id = db_save('modulettes');
	url_drop('id');
}

echo drawTop();

if (url_id()) {
	//form
	$f = new form('modulettes', @$_GET['id']);
	langUnsetFields($f, 'title');
	langTranslateCheckbox($f);
	echo $f->draw();
} else {
	//modulettes list
	$t = new table('modulettes', drawHeader());
	$t->set_column('is_active', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('pages', 'r');
	
	$result = db_table('SELECT m.id, m.title' . langExt() . ' title, m.is_active, (SELECT COUNT(*) FROM pages p WHERE p.modulette_id = m.id) pages FROM modulettes m ORDER BY m.title' . langExt());
	
	foreach ($result as &$r) {
		$r['is_active']	= draw_form_checkbox('is_active', $r['is_active'], false, 'ajax_set(\'modulettes\', \'is_active\', ' . $r['id'] . ', ' . abs($r['is_active'] - 1) . ');');
		$r['title']			= draw_link('modulettes.php?id=' . $r['id'], $r['title']);
		$r['pages']			= draw_link('pages.php?modulette_id=' . $r['id'], format_q($r['pages'], 'page'));
	}
	
	echo $t->draw($result, 'No modulettes');
}

echo drawBottom();
?>