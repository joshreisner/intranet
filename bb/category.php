<?php
include('include.php');

echo drawTop();

if (url_id()) {
	//get a particular topic
	$title = db_grab('SELECT title' . langExt() . ' title FROM bb_topics_types WHERE id = ' . $_GET['id']);
	$where = 't.type_id = ' . $_GET['id'];
} else {
	$title = 'Uncategorised Topics';
	$where = 't.type_id IS NULL';
}

$result = db_table('SELECT 
		t.id,
		t.title' . langExt() . ' topic,
		t.is_admin,
		t.thread_date last_post,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topic_id AND f.is_active = 1) replies,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
	FROM bb_topics t
	JOIN users u ON u.id = t.created_user
	' . getChannelsWhere('bb_topics', 't', 'topic_id') . ' AND ' . $where . '
	ORDER BY t.thread_date DESC', 15);
$t = new table('bb_topics', drawHeader(false, $title));
$t->col('topic', 'l', getString('topic'));
$t->col('starter', 'l', getString('starter'));
$t->col('replies', 'c', getString('replies'));
$t->col('last_post', 'r', getString('last_post'));

foreach($result as &$r) {
	$r['link'] = 'topic.php?id=' . $r['id'];
	$r['class'] = 'thread';
	if ($r['is_admin']) $r['class'] .= ' admin';
	$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
	$r['last_post'] = format_date($r['last_post']);
	$r['topic'] = draw_link($r['link'], $r['topic']);
}
echo $t->draw($result, 'No topics have been added to this category yet.<br>Why not <a href="./#bottom">be the first</a>');

echo drawBottom();
?>