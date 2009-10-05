<?php
include("../include.php");

function bbDrawTopic($topic_id) {
	//get topic 
	$r = db_grab("SELECT 
			t.title,
			t.description,
			u.id,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname,
			t.type_id,
			y.title type,
			t.created_date
		FROM bb_topics t
		JOIN users u ON t.created_user = u.id
		LEFT JOIN bb_topics_types y ON t.type_id = y.id
		WHERE t.id = " . $topic_id);
	
	//draw top
	$caption = "";
	if (getOption("bb_types") && $r["type"]) {
		$caption .= "Category: " . draw_link("category.php?id=" . $r["type_id"], $r["type"]) . "<br>";
	}
	if (getOption("channels")) {
		$channels = db_array("SELECT c.title_en FROM channels c JOIN bb_topics_to_channels t2c ON c.id = t2c.channel_id WHERE t2c.topic_id = $topic_id ORDER BY title_en");
		if ($channels) $caption .= "Networks: " . implode(", ", $channels);
	}
	if ($caption) $r["description"] .= "<span class='light caption'>" . $caption . "</span>";
	
	$return = drawThreadTop($r["title"], $r["description"], $r["id"], $r["firstname"] . " " . $r["lastname"], $r["created_date"]);

	//append followups
	$followups = db_query("SELECT
				f.id,
				f.description,
				u.id,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				f.created_date as postedDate,
				f.created_user as user_id
			FROM bb_followups f
			JOIN users u ON u.id = f.created_user
			WHERE f.is_active = 1 AND f.topic_id = $topic_id
			ORDER BY f.created_date");
	while ($f = db_fetch($followups)) { 
		$return .= drawThreadComment($f["description"], $f["user_id"], $f["firstname"] . " " . $f["lastname"], $f["postedDate"]);
	}
	
	return $return;
}

function drawTopicForm() {
	global $_GET, $module_admin;
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
	return $f->draw(false, false);
}

function bbDrawRss() {
	global $_josh;
	
	$items = array();
	
	$topics = db_query("SELECT 
			t.id,
			t.title,
			t.description,
			t.is_admin,
			t.thread_date,
			(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topic_id AND f.is_active = 1) replies,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname,
			u.email
		FROM bb_topics t
		JOIN users u ON u.id = t.created_user
		WHERE t.is_active = 1 
		ORDER BY t.thread_date DESC", 15);
	
	while ($t = db_fetch($topics)) {
		if ($t["is_admin"]) $t["title"] = "ADMIN: " . $t["title"];
		if ($t["replies"] == 1) {
			$t["title"] .= " (" . $t["replies"] . " comment)";
		} elseif ($t["replies"] > 1) {
			$t["title"] .= " (" . $t["replies"] . " comments)";
		}
		$items[] = array(
			"title" => $t["title"],
			"description" => $t["description"],
			"link" => url_base() . "/bb/topic.php?id=" . $t["id"],
			"date" => $t["thread_date"],
			"author" => $t["email"] . " (" . $t["firstname"] . " " . $t["lastname"] . ")"
		);
	}

	file_rss("Bulletin Board: Last 15 Topics", url_base() . "/bb/", $items, $_josh["write_folder"] . "/rss/bb.xml");
}

?>