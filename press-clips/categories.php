<?php
include("../include.php");
echo drawTop();

if (url_id()) {
	$title = db_grab("SELECT title FROM press_clips_types WHERE id = " . $_GET["id"]);
	$result = db_table('SELECT c.id, c.title, c.pub_date, t.title type, c.publication, ISNULL(c.created_date, c.updated_date) updated FROM press_clips c JOIN press_clips_types t ON c.type_id = t.id ' . getChannelsWhere('press_clips', 'c', 'clip_id') . ' AND c.type_id = ' . $_GET["id"] . ' ORDER BY updated DESC');
	$t = new table('press_clips', drawHeader(false, $title));
	$t->col('title');
	$t->col('publication');
	$t->col('pub_date', 'r');
	foreach ($result as &$r) {
		$r['title'] = draw_link('clip.php?id=' . $r['id'], format_string($r['title'], 50));
		$r['pub_date'] = format_date($r['pub_date']);
	}
	echo $t->draw($result, 'There are no clips tagged <i>' . $title . '</i>.');	
	
} else {
	$t = new table('press_clips', drawHeader());
	$t->col('category', 'l', getString('category'));
	$t->col('clips', 'r', getString('clips'));
	$result = db_table('SELECT t.id, t.title' . langExt() . ' category, (SELECT COUNT(*) FROM press_clips c WHERE c.type_id = t.id) clips FROM press_clips_types t ORDER BY t.precedence');
	foreach ($result as &$r) {
		$r['category'] = draw_link(url_query_add(array('id'=>$r['id']), false), $r['category']);
	}
	echo $t->draw($result);
}

echo drawBottom();
?>