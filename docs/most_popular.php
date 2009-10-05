<?
include("../include.php");
drawTop();


$result = db_table('SELECT 
		d.id,
		d.title,
		(SELECT COUNT(*) FROM docs_views v WHERE v.documentID = d.id) downloads,
		i.icon,
		i.description alt
	FROM docs d
	JOIN docs_types i ON d.type_id = i.id
	' . getChannelsWhere('docs', 'd', 'doc_id') . '
	ORDER BY downloads DESC', 20);
	
$t = new table('docs', drawPageName());
$t->set_column('icon', 'd', '&nbsp;');
$t->set_column('title');
$t->set_column('downloads', 'r');

foreach ($result as &$r) {
	$link = 'info.php?id=' . $r['id'];
	$r['icon'] = draw_img($r['icon'], $link);
	$r['title'] = draw_link($link, $r['title']);
}

echo $t->draw($result);

drawBottom();?>