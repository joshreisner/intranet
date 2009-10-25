<?php
include("../../include.php");

if (url_id('module_id')) {
	$result = db_table('SELECT p.id, p.title, p.url, p.is_hidden FROM pages p WHERE module_id = ' . $_GET['module_id'] . ' ORDER BY p.precedence');
} elseif (url_id('modulette_id')) {
	$result = db_table('SELECT p.id, p.title, p.url, p.is_hidden FROM pages p WHERE modulette_id = ' . $_GET['modulette_id'] . ' ORDER BY p.precedence');
} else {
	url_change('./');
}

echo drawTop();

//pages list
$t = new table('pages', drawHeader());
$t->set_column('is_hidden', 'd', '&nbsp;');
$t->set_column('draggy', 'd', '&nbsp;');
$t->set_column('title', 'l', getString('title'));
$t->set_column('url');
$t->set_draggable('draggy');

foreach ($result as &$r) {
	$r['is_hidden']	= draw_form_checkbox('foo', !$r['is_hidden'], false, 'ajax_set(\'pages\', \'is_hidden\', ' . $r['id'] . ', ' . abs($r['is_hidden'] - 1) . ');');
	$r['draggy']		= draw_img('/images/icons/move.png');
	$r['title']			= draw_link('page.php?id=' . $r['id'], $r['title']);
	if (empty($r['url'])) $r['url'] = 'index.php';
}

echo $t->draw($result, 'No pages');

echo drawBottom();
?>