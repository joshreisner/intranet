<?php
include("../include.php");

echo drawTop();

$result = db_table('SELECT 
		c.id, 
		c.title' . langExt() . ' title, 
		c.pub_date, 
		t.title' . langExt() . ' "group", 
		c.publication' . langExt() . ' publication, 
		' . db_updated('c') . '
	FROM press_clips c 
	JOIN press_clips_types t ON c.type_id = t.id 
	' . getChannelsWhere('press_clips', 'c', 'clip_id') . '
	ORDER BY t.title, pub_date DESC', 20);

$t = new table('press-clips', drawHeader((($page['is_admin']) ? array('edit.php'=>getString('add_new')) : false)));
$t->col('title', 'l', getString('title'));
foreach ($result as &$r) {
	$r['title'] = draw_link("clip.php?id=" . $r["id"], format_string($r["title"], 80)) . '<br>' . 
	$r["publication"] . ' <span class="light">' . format_date($r["pub_date"]) . '</span>';
}
echo $t->draw($result, getString('pressclips_recent_empty'));

include("edit.php");

echo drawBottom();
?>