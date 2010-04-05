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
	} elseif (getOption('bb_notifypost') && getOption('channels')) {
		//get addresses of everyone with indicated interests and send
		$channels = array_post_checkboxes('channels');
		$emails = db_array('SELECT DISTINCT u.email FROM users u JOIN users_to_channels_prefs u2cp ON u.id = u2cp.user_id WHERE u2cp.channel_id IN (' . implode(',', $channels) . ')');
		emailUsers($emails, $_POST['title'], bbDrawTopic($id), 2);
	}
	
	bbDrawRss();
	url_change();
}

echo drawTop(drawSyndicateLink('bb'));

echo draw_div('bb_topics', bbDrawTable(15));

echo draw_javascript('function_attach(setInterval(refreshBB, 60000));');

//add new topic
echo '<a name="bottom"></a>';
echo drawTopicForm();

echo drawBottom();

?>