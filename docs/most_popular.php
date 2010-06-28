<?php
include("../include.php");
echo drawTop();

$result = db_table('SELECT 
		d.id,
		d.title' . langExt() . ' title,
		(SELECT COUNT(*) FROM docs_views v WHERE v.documentID = d.id) downloads,
		i.extension,
		i.description alt
	FROM docs d
	JOIN docs_types i ON d.type_id = i.id
	' . getChannelsWhere('docs', 'd', 'doc_id') . '
	ORDER BY downloads DESC', 20);
	
$t = new table('docs', drawHeader());
$t->set_column('icon', 'd', '&nbsp;');
$t->set_column('title', 'l', getString('title'));
$t->set_column('downloads', 'r', getString('downloads'));

foreach ($result as &$r) {
	$link = 'info.php?id=' . $r['id'];
	$r['icon'] = file_icon($r['extension'], $link);
	$r['title'] = draw_link($link, $r['title']);
}

echo $t->draw($result, getString('documents_empty'));

echo drawBottom();
?>