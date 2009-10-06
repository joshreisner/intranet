<?php
include("../include.php");

echo drawTop();

$result = db_table('SELECT 
		c.id, 
		c.title, 
		c.pub_date, 
		t.title "group", 
		c.publication, 
		' . db_updated('c') . '
	FROM press_clips c 
	JOIN press_clips_types t ON c.type_id = t.id 
	' . getChannelsWhere('press_clips', 'c', 'clip_id') . '
	ORDER BY t.title, pub_date DESC', 20);

$t = new table('press-clips', drawHeader());
$t->col('title');
foreach ($result as &$r) {
	$r['title'] = draw_link("clip.php?id=" . $r["id"], format_string($r["title"], 80)) . '<br>' . 
	$r["publication"] . ' <span class="light">' . format_date($r["pub_date"]) . '</span>';
}
echo $t->draw($result, 'There are no recent clips.');

include("edit/index.php");

echo drawBottom();
?>