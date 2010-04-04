<?php
include('include.php');

echo drawTop();

if (url_id()) {
	//get a particular topic
	$title = db_grab('SELECT title' . langExt() . ' title FROM bb_topics_types WHERE id = ' . $_GET['id']);
	$where = ' AND t.type_id = ' . $_GET['id'];
} else {
	$title = 'Uncategorised Topics';
	$where = ' AND t.type_id IS NULL';
}

echo bbDrawTable(false, $where, $title);

echo drawBottom();
?>