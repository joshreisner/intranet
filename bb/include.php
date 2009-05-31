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
	if (getOption("bb_types") && $r["type"]) $r["description"] = $r["description"] . "<span class='light'>Category: " . draw_link("category.php?id=" . $r["type_id"], $r["type"]) . "</span>";
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

	file_rss("Bulletin Board: Last 15 Topics", url_base() . "/bb/", $items, $_josh["write_folder"] . "/syndicate/bb.xml");
}

?>