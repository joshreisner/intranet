<? include("include.php");

if ($posting) {
	$_POST["description"] = $_POST["message"];
	$_POST["topic_id"] = $_GET["id"];
	$id = db_save("bb_followups", false);
	db_query("UPDATE bb_topics SET thread_date = GETDATE() WHERE id = " . $_POST["topic_id"]);
	
	//send followup email to all topic contributors
	if (getOption("bb_notifyfollowup")) {
		$message = "There has been an update on a bulletin board topic you contributed to.<br>Click <a href='" . url_base() . "/bb/topic.php?id=" . $_POST["topic_id"] . "'>here to view</a> the topic.";
		
		//get topic poster email
		$emails = array(db_grab("SELECT u.email FROM bb_topics t JOIN users u ON t.created_user = u.id WHERE t.id = " . $_POST["topic_id"]));

		//get followup poster emails
		$emails = array_merge($emails, db_array("SELECT u.email FROM bb_followups f JOIN users u ON u.id = f.created_user WHERE f.is_active = 1 AND f.topic_id = " . $_POST["topic_id"]));

		//emailUsers($emails, "Followup on Bulletin Board Post", bbDrawTopic($_GET["id"]), 2, $message);
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
if (!$r = db_grab("SELECT 
		t.title,
		t.description,
		t.created_date,
		t.is_admin,
		t.type_id,
		y.title type,
		u.id user_id,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
		FROM bb_topics t
		JOIN users u ON t.created_user = u.id
		LEFT JOIN bb_topics_types y ON t.type_id = y.id
		WHERE t.id = " . $_GET["id"])) url_change("/bb/");

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
	echo drawHeaderRow($page['breadcrumbs'] . $r["title"], 2, "edit", "edit.php?id=" . $_GET["id"], "delete", "javascript:checkDelete();");
} else {
	echo drawHeaderRow($page['breadcrumbs'] . $r["title"], 2, "add a followup", "#bottom");
}
echo bbDrawTopic($_GET["id"]);
echo drawThreadCommentForm(false);
echo drawTableEnd();
echo drawBottom();

?>