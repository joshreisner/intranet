<?php
include('include.php');

if ($posting) {
	error_debug('handling bb post', __file__, __line__);
	format_post_bits('is_admin');
	langTranslatePost('title,description');
	$id = db_save('bb_topics');
	db_query('UPDATE bb_topics SET thread_date = GETDATE(), replies = (SELECT COUNT(*) FROM bb_followups WHERE topic_id = ' . $id . ') WHERE id = ' . $id);
	if (getOption('channels')) db_checkboxes('channels', 'bb_topics_to_channels', 'topic_id', 'channel_id', $id);
	
	//notification
	if ($_POST['is_admin'] == '1') {
		//get addresses of everyone & send with message
		//emailUser(array('josh@joshreisner.com', 'test@joshreisner.com'), $_POST['title'], drawEmail(bbDrawTopic($id, true)));
		emailUser(db_array('SELECT email FROM users WHERE is_active = 1'), $_POST['title'], drawEmail(bbDrawTopic($id, true)));
	} elseif (getOption('bb_notifypost') && getOption('channels') && getOption('languages')) {
		//get addresses of everyone with indicated interests and send
		$channels = array_post_checkboxes('channels');
		
		$languages = db_table('SELECT id, code FROM languages');
		foreach ($languages as $l) {
			$addresses = db_array('SELECT DISTINCT u.email FROM users u JOIN users_to_channels_prefs u2cp ON u.id = u2cp.user_id WHERE u.is_active = 1 AND u.language_id = ' . $l['id'] . ' AND u2cp.channel_id IN (' . implode(',', $channels) . ')');
						
			$topic = db_grab('SELECT 
						ISNULL(u.nickname, u.firstname) firstname, 
						u.lastname, 
						t.title' . langExt($l['code']) . ' title, 
						t.description' . langExt($l['code']) . ' description, 
						y.title' . langExt($l['code']) . ' type,
						t.created_date
					FROM bb_topics t
					LEFT JOIN bb_topics_types y ON t.type_id = y.id
					JOIN users u ON t.created_user = u.id
					WHERE t.id = ' . $id);
			
			$channels_text = db_array('SELECT title' . langExt($l['code']) . ' FROM channels WHERE id IN (' . implode(',', $channels) . ')');
			$channels_text = implode(', ', $channels_text);
			
			$message = 
				'<p style="font-weight:bold;">' . $topic['firstname'] . ' ' . $topic['lastname'] . ' ' . getString('bb_notify', $l['code']) . '</p>
				<p>' . getString('title', $l['code']) . ': ' . draw_link(url_base() . '/bb/topic.php?id=' . $id, $topic['title']) . '</p>
				<p>' . getString('channels_label', $l['code']) . ': ' . $channels_text . '</p>';
			if ($topic['type']) $message .= '<p>' . getString('category', $l['code']) . ': ' . $topic['type'] . '</p>';
			$message .= '<div style="color:#555; border-top:1px dotted #555; padding-top:5px; margin-top:5px;">' . $topic['description'] . '</div>';
	 		
			emailUser($addresses, $topic['title'], $message);
		}
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