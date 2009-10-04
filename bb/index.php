<?php
include('include.php');

if ($posting) {
	error_debug('handling bb post', __file__, __line__);
	format_post_bits('is_admin');
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

$where = 'WHERE t.is_active = 1';
if (getOption('channels') && $_SESSION['channel_id']) $where = 'JOIN bb_topics_to_channels t2c ON t.id = t2c.topic_id WHERE t.is_active = 1 AND t2c.channel_id = ' . $_SESSION['channel_id'];

$t = new table('bb_topics', drawPageName() . '<a class="right" href="#bottom">add new</a>');
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
	ORDER BY t.thread_date DESC', 15);

foreach ($result as &$r) {
	$r['class'] = 'thread';
		$r['link'] = 'topic.php?id=' . $r['id'];
	$r['topic'] = draw_link($r['link'], $r['topic']);
	$r['starter'] = $r['firstname'] . ' ' . $r['lastname'];
	$r['last_post'] = format_date($r['last_post']);
}

echo $t->draw($result, 'No topics have been added yet.  Why not <a href="#bottom">be the first</a>?');

//add new topic
echo '<a name="bottom"></a>';
$f = new form('bb_topics', @$_GET['id'], 'Contribute a New Topic');
if ($module_admin) {
	$f->set_field(array('name'=>'created_user', 'class'=>'admin', 'type'=>'select', 'sql'=>'SELECT id, CONCAT_WS(", ", lastname, firstname) FROM users WHERE is_active = 1 ORDER BY lastname, firstname', 'default'=>$_SESSION['user_id'], 'required'=>true, 'label'=>'Posted By'));
	$f->set_field(array('name'=>'is_admin', 'class'=>'admin', 'type'=>'checkbox'));
} else {
	$f->unset_fields('is_admin');
}
if (getOption('channels')) $f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>'Networks', 'options_table'=>'channels', 'linking_table'=>'bb_topics_to_channels', 'object_id'=>'topic_id', 'option_id'=>'channel_id'));
if (getOption('bb_types')) $f->set_field(array('name'=>'type_id', 'type'=>'select', 'sql'=>'SELECT id, title FROM bb_topics_types', 'label'=>'Category'));
$f->set_order('created_user,is_admin,title,type_id,channels,description');
$f->unset_fields('thread_date');
echo $f->draw(false, false);


drawBottom(); 
?>