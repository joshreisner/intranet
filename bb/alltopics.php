<?php
include('include.php');

drawTop();

echo drawSyndicateLink('bb');

$where = 'WHERE t.is_active = 1';
if (getOption('channels') && $_SESSION['channel_id']) $where = 'JOIN bb_topics_to_channels t2c ON t.id = t2c.topic_id WHERE t.is_active = 1 AND t2c.channel_id = ' . $_SESSION['channel_id'];

$t = new table('bb_topics', drawPageName());
$t->set_column('topic');
$t->set_column('starter');
$t->set_column('replies', 'c');
$t->set_column('last_post', 'r');

$result = db_table('SELECT 
		t.id,
		t.title topic,
		t.is_admin,
		t.thread_date last_post,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topic_id AND f.is_active = 1) replies,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
	FROM bb_topics t
	JOIN users u ON u.id = t.created_user
	' . $where . '
	ORDER BY t.thread_date DESC');

foreach ($result as &$r) {
	$r['class'] = 'thread';
		$r['link'] = 'topic.php?id=' . $r['id'];
	$r['topic'] = draw_link($r['link'], $r['topic']);
	$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
	$r['last_post'] = format_date($r['last_post']);
}

echo $t->draw($result, 'No topics have been added yet.  Why not <a href="./#bottom">be the first</a>?');

drawBottom(); 
?>