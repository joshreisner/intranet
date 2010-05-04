<? include("include.php");

if ($posting) {
	$_POST['translations_do'] = true;
	langTranslatePost('description');
	$_POST["topic_id"] = $_GET["id"];
	$id = db_save("bb_followups", false);
	db_query("UPDATE bb_topics SET thread_date = GETDATE() WHERE id = " . $_POST["topic_id"]);
	
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
} elseif (isset($_GET["delete"])) {
	db_delete("bb_topics");
	bbDrawRss();
	url_change("/bb/");
} elseif (isset($_GET["deleteFollowupID"])) {
	db_delete("bb_followups", $_GET["deleteFollowupID"]);
	bbDrawRss();
	url_query_drop("deleteFollowupID");
}

//get topic data
if (!$r = db_grab('SELECT 
		t.title' . langExt() . ' title,
		t.description' . langExt() . ' description,
		t.created_date,
		t.is_admin,
		t.type_id,
		y.title' . langExt() . ' type,
		u.id user_id,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
		FROM bb_topics t
		JOIN users u ON t.created_user = u.id
		LEFT JOIN bb_topics_types y ON t.type_id = y.id
		WHERE t.id = ' . $_GET["id"])) url_change("/bb/");

echo drawTop();
echo drawSyndicateLink("bb");

$isPoster = ($r["user_id"] == $_SESSION["user_id"]) ? true : false;

if ($r["is_admin"] == 1) echo drawMessage(getString("topic_admin"));

echo draw_javascript('
	function checkDelete() {
		if (confirm("Are you sure you want to delete this topic?")) location.href="' . $_josh["request"]["path_query"] . '&delete=true";
	}
	function checkDeleteFollowup(id) {
		if (confirm("Are you sure you want to delete this followup?")) location.href="' . $_josh["request"]["path_query"] . '&deleteFollowupID=" + id;
	}
	function validateComment(form) {
		if (!form.description.value.length || (form.description.value == "<p>&nbsp;</p>")) return false;
		return true;
	}
');

echo drawTableStart();
if ($page['is_admin'] || $isPoster) {
	echo drawHeaderRow($page['breadcrumbs'] . format_string($r["title"], 40), 2, getString('edit'), "edit.php?id=" . $_GET["id"], getString('delete'), "javascript:checkDelete();");
} else {
	echo drawHeaderRow($page['breadcrumbs'] . format_string($r["title"], 40), 2, getString('add_followup'), "#bottom");
}
echo bbDrawTopic($_GET["id"]);
echo drawThreadCommentForm(false);
echo drawTableEnd();
echo drawBottom();

?>