<?php
include('include.php');

if ($posting) {
	error_debug('handling bb post', __file__, __line__);
	format_post_bits('is_admin');
	langTranslatePost('title,description');
	$id = db_save('bb_topics');
	db_query('UPDATE bb_topics SET thread_date = GETDATE() WHERE id = ' . $id);
	if (getOption('channels')) db_checkboxes('channels', 'bb_topics_to_channels', 'topic_id', 'channel_id', $id);
	
	//notification
	if ($_POST['is_admin'] == '1') {
		//get addresses of everyone & send with message
		//emailUsers(db_array('SELECT email FROM users WHERE is_active = 1'), $_POST['title'], bbDrawTopic($id), 2, getString('bb_admin'));
	} elseif (getOption('bb_notifypost')) {
		//get addresses of everyone with notify_topics checked and send
		//emailUsers(db_array('SELECT email FROM users WHERE is_active = 1 AND notify_topics = 1'), $_POST['title'], bbDrawTopic($id), 2);
	}
	
	bbDrawRss();
	url_change();
}

drawTop();
echo draw_autorefresh(5); //todo eliminate
echo drawSyndicateLink('bb');

$t = new table('bb_topics', drawHeader(array('#bottom'=>'add new')));
$t->set_column('topic', 'l', getString('topic'));
$t->set_column('starter', 'l', getString('starter'));
$t->set_column('replies', 'c', getString('replies'));
$t->set_column('last_post', 'r', getString('last_post'));

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
	ORDER BY t.thread_date DESC', 15);

foreach ($result as &$r) {
	$r['class'] = 'thread';
	if ($r['is_admin']) $r['class'] .= ' admin';
	$r['link'] = 'topic.php?id=' . $r['id'];
	$r['topic'] = draw_link($r['link'], $r['topic']);
	$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
	$r['last_post'] = format_nobr(format_date($r['last_post']));
}

echo $t->draw($result, getString('no_topics'));

//add new topic
echo '<a name="bottom"></a>';
echo drawTopicForm();

drawBottom(); 
?>