<?php
include('include.php');

echo drawTop();

echo drawSyndicateLink('bb');

$t = new table('bb_topics', drawHeader());
$t->set_column('topic');
$t->set_column('starter');
$t->set_column('replies', 'c');
$t->set_column('last_post', 'r');

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
	' . getChannelsWhere('bb_topics', 't', 'topic_id') . '
	ORDER BY t.thread_date DESC');

foreach ($result as &$r) {
	$r['class'] = 'thread';
	if ($r['is_admin']) $r['class'] .= ' admin';
	$r['link'] = 'topic.php?id=' . $r['id'];
	$r['topic'] = draw_link($r['link'], $r['topic']);
	$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
	$r['last_post'] = format_date($r['last_post']);
}

echo $t->draw($result, 'No topics have been added yet.  Why not <a href="./#bottom">be the first</a>?');

echo drawBottom(); 
?>