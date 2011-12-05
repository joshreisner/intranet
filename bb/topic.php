<?php
include("include.php");

url_query_require();

if ($posting) {
	$_POST['translations_do'] = true;
	langTranslatePost('description');
	$_POST["topic_id"] = $_GET["id"];
	$id = db_save("bb_followups", false);
	db_query('UPDATE bb_topics SET thread_date = GETDATE(), replies = (SELECT COUNT(*) FROM bb_followups WHERE topic_id = ' . $_POST['topic_id'] . ') WHERE id = ' . $_POST['topic_id']);
	
	//send followup email to all topic contributors
	if (getOption("bb_notifyfollowup")) {
		$addresses = array();
		$languages = db_array('SELECT code FROM languages');
		foreach ($languages as $l) $addresses[$l] = array();
				
		//get topic poster email, put in correct bucket
		$poster = db_grab("SELECT 
				u.email, 
				l.code 
			FROM bb_topics t 
			JOIN users u ON t.created_user = u.id 
			JOIN languages l ON u.language_id = l.id
			WHERE u.is_active = 1 AND t.id = " . $_POST["topic_id"]);
		$addresses[$poster['code']][] = $poster['email'];
		
		//get followup poster emails
		$repliers = db_table("SELECT 
				u.email,
				l.code
			FROM bb_followups f 
			JOIN users u ON u.id = f.created_user 
			JOIN languages l ON u.language_id = l.id
			WHERE u.is_active = 1 AND f.is_active = 1 AND f.topic_id = " . $_POST["topic_id"]);
		foreach ($repliers as $r) $addresses[$r['code']][] = $r['email'];

		foreach ($addresses as $lang=>$emails) {
			$topic = db_grab('SELECT 
						t.title' . langExt($lang) . ' title, 
						y.title' . langExt($lang) . ' type,
						t.created_date
					FROM bb_topics t
					LEFT JOIN bb_topics_types y ON t.type_id = y.id
					WHERE t.id = ' . $_POST['topic_id']);
					
			$reply = db_grab('SELECT
						f.description' . langExt($lang) . ' description,
						ISNULL(u.nickname, u.firstname) firstname, 
						u.lastname
					FROM bb_followups f
					JOIN users u ON f.created_user = u.id
					WHERE f.id = ' . $id);
						
			$channels_text = db_array('SELECT c.title' . langExt($lang) . ' FROM bb_topics_to_channels t2c JOIN channels c ON t2c.channel_id = c.id WHERE t2c.topic_id = ' . $_POST['topic_id']);
			$channels_text = implode(', ', $channels_text);
			
			$message = 
				'<p style="font-weight:bold;">' . $reply['firstname'] . ' ' . $reply['lastname'] . ' ' . getString('bb_followup', $lang) . '</p>
				<p>' . getString('title', $lang) . ': ' . draw_link(url_base() . '/bb/topic.php?id=' . $id, $topic['title']) . '</p>
				<p>' . getString('channels_label', $lang) . ': ' . $channels_text . '</p>';
			if ($topic['type']) $message .= '<p>' . getString('category', $lang) . ': ' . $topic['type'] . '</p>';
			$message .= '<div style="color:#555; border-top:1px dotted #555; padding-top:5px; margin-top:5px;">' . $reply['description'] . '</div>';
			
			emailUser($emails, 'RE: ' . $topic['title'], $message);
		}
	}
	
	bbDrawRss();
	url_change();
} elseif (isset($_GET['delete'])) {
	db_delete('bb_topics');
	bbDrawRss();
	url_change('/bb/');
} elseif (isset($_GET['deleteFollowupID'])) {
	db_delete('bb_followups', $_GET['deleteFollowupID']);
	bbDrawRss();
	url_query_drop('deleteFollowupID');
}

//get topic data
if (!$r = bbDrawTopic($_GET['id'])) url_change('/bb/');

echo drawTop();

echo $r;

echo drawBottom();