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
		//emailUsers(db_array('SELECT email FROM users WHERE is_active = 1'), $_POST['title'], bbDrawTopic($id), 2, getString('topic_admin'));
	} elseif (getOption('bb_notifypost')) {
		//get addresses of everyone with notify_topics checked and send
		//emailUsers(db_array('SELECT email FROM users WHERE is_active = 1 AND notify_topics = 1'), $_POST['title'], bbDrawTopic($id), 2);
	}
	
	bbDrawRss();
	url_change();
}

echo drawTop(drawSyndicateLink('bb'));

echo draw_div('bb_topics', bbDrawTable(15));

echo draw_javascript('event_add(setInterval(refreshBB, 60000));');

//add new topic
echo '<a name="bottom"></a>';
echo drawTopicForm();

echo drawBottom();

?>