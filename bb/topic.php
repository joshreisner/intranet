<? include("../include.php");

if ($posting) {
	$_POST["description"] = $_POST["message"];
	$_POST["topic_id"] = $_GET["id"];
	$_GET["id"] = false; //shameless hack for db_save
	$id = db_save("bb_followups");
	db_query("UPDATE bb_topics SET thread_date = GETDATE() WHERE id = " . $_POST["topic_id"]);
	
	//send followup email to all topic posters
	$message = drawEmailHeader() . drawMessage("There has been an update on a bulletin board topic you contributed to.  Click 
		<a href='" . url_base() . "/bb/topic.php?id=" . $_POST["topic_id"] . "'>here to view</a> the topic.");
	$message .= '<table class="center">';
	$r = db_grab("SELECT 
			t.title,
			t.description,
			t.created_date,
			t.is_admin,
			u.id,
			u.email,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname
			FROM bb_topics t
			JOIN users u ON t.created_user = u.id
			WHERE t.id = " . $_POST["topic_id"]);
	$emails = array($r["email"]);
	$message .= drawHeaderRow($r["title"], 2);
	$message .= drawThreadTop($r["title"], $r["description"], $r["user_id"], $r["firstname"] . " " . $r["lastname"], $r["created_date"]);
	$followups = db_query("SELECT
				f.id,
				f.description,
				u.id,
				u.email,
				ISNULL(u.nickname, u.firstname) firstname,
				u.lastname,
				f.created_date as postedDate,
				f.created_user as user_id
			FROM bb_followups f
			JOIN users u ON u.id = f.created_user
			WHERE f.is_active = 1 AND f.topic_id = {$_POST["topic_id"]}
			ORDER BY f.created_date");
	while ($f = db_fetch($followups)) { 
		$emails[] = $f["email"];
		$message .= drawThreadComment($f["description"], $f["user_id"], $f["firstname"] . " " . $f["lastname"], $f["postedDate"]);
	}
	$message .= '</table>' . drawEmailFooter();
	$emails = array_unique($emails);
	//unset($emails[$_SESSION["email"]]); //don't send email to current user
	foreach ($emails as $e) email($e, $message, "Followup to Bulletin Board Topic");
	
	syndicateBulletinBoard();
	url_change();
}

//set topic and followups to deleted
if (isset($_GET["delete"])) {
	db_query("UPDATE bb_topics SET 
				is_active = 0,
				deleted_date = GETDATE(),
				deleted_user = {$_SESSION["user_id"]}
			  WHERE id = " . $_GET["id"]);
	syndicateBulletinBoard();
	url_change("/bb/");
} elseif (isset($_GET["deleteFollowupID"])) {
	db_query("UPDATE bb_followups SET 
				is_active = 0,
				deleted_date = GETDATE(),
				deleted_user = {$_SESSION["user_id"]}
			  WHERE ID = " . $_GET["deleteFollowupID"]);
	url_query_drop("deleteFollowupID");
}

//get topic data
$r = db_grab("SELECT 
		t.title,
		t.description,
		t.created_date,
		t.is_admin,
		u.id user_id,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
		FROM bb_topics t
		JOIN users u ON t.created_user = u.id
		WHERE t.id = " . $_GET["id"]);

//check that it exists
	if (empty($r)) url_change("/bb/");

drawTop();
echo drawSyndicateLink("bb");

$isPoster = ($r["user_id"] == $_SESSION["user_id"]) ? true : false;


$r["description"] = htmlwrap($r["description"]);

if ($r["is_admin"] == 1) echo drawMessage(getString("bb_admin"));
?>
<script language="javascript">
	<!--
	function checkDelete() {
		if (confirm("Are you sure you want to delete this topic?")) location.href="<?=$_josh["request"]["path_query"]?>&delete=true";
	}
	function checkDeleteFollowup(id) {
		if (confirm("Are you sure you want to delete this followup?")) location.href="<?=$_josh["request"]["path_query"]?>&deleteFollowupID=" + id;
	}
	function validateComment(form) {
		if (!form.description.value.length || (form.description.value == '<p>&nbsp;</p>')) return false;
		return true;
	}

	//-->
</script>

<table class="left" cellspacing="1">
	<?php
	if ($module_admin || $isPoster) {
		echo drawHeaderRow($r["title"], 2, "edit", "edit.php?id=" . $_GET["id"], "delete", "javascript:checkDelete();");
	} else {
		echo drawHeaderRow($r["title"], 2, "add a followup", "#bottom");
	}
	echo drawThreadTop($r["title"], $r["description"], $r["user_id"], $r["firstname"] . " " . $r["lastname"], $r["created_date"]);

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
			WHERE f.is_active = 1 AND f.topic_id = {$_GET["id"]}
			ORDER BY f.created_date");
	while ($f = db_fetch($followups)) { 
		echo drawThreadComment($f["description"], $f["user_id"], $f["firstname"] . " " . $f["lastname"], $f["postedDate"]);
	}
	echo drawThreadCommentForm(false);

echo '</table>';

drawBottom();?>