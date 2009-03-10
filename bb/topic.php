<? include("../include.php");

if ($posting) {
	$_POST["description"] = format_html($_POST["message"]);
	$_POST["topicID"] = $_GET["id"];
	$_GET["id"] = false; //stupid hack for db_enter
	$id = db_enter("bb_followups", "topicID |description");
	db_query("UPDATE bb_topics SET threadDate = GETDATE() WHERE id = " .  db_grab("SELECT topicID FROM bb_followups WHERE id = " . $id));
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
		u.user_id,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
		FROM bb_topics t
		JOIN users u ON t.created_user = u.user_id
		WHERE t.id = " . $_GET["id"]);

//check that it exists
	if (empty($r)) url_change("/bb/");

drawTop();
echo drawSyndicateLink("bb");

$isPoster = ($r["user_id"] == $_SESSION["user_id"]) ? true : false;


$r["description"] = htmlwrap($r["description"]);

//if ($_GET["id"] == 7966) echo drawServerMessage("<b>Note</b>: This comments on this post are organized in reverse-chronological order.");

if ($r["is_admin"]) echo drawServerMessage(getString("bb_admin"));
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
	if ($is_admin || $isPoster) {
		echo drawHeaderRow($r["title"], 2, "edit", "edit.php?id=" . $_GET["id"], "delete", "javascript:checkDelete();");
	} else {
		if ($r["is_admin"]) {
			echo drawHeaderRow($r["title"], 2);
		} else {
			echo drawHeaderRow($r["title"], 2, "add a followup", "#bottom");
		}
	}
	echo drawThreadTop($r["title"], $r["description"], $r["user_id"], $r["firstname"] . " " . $r["lastname"], $r["created_date"]);
	//get replies
	//$direction = ($_GET["id"] == 7966) ? "DESC" : "ASC";
	if (!$r["is_admin"]) {
		$followups = db_query("SELECT
					f.id,
					f.description,
					u.user_id,
					ISNULL(u.nickname, u.firstname) firstname,
					u.lastname,
					f.created_date as postedDate,
					f.created_user as user_id
				FROM bb_followups f
				JOIN users u ON u.user_id = f.created_user
				WHERE f.is_active = 1 AND f.topicID = {$_GET["id"]}
				ORDER BY f.created_date");
		while ($f = db_fetch($followups)) { 
			echo drawThreadComment($f["description"], $f["user_id"], $f["firstname"] . " " . $f["lastname"], $f["postedDate"]);
		}
		echo drawThreadCommentForm(false);
	}
echo '</table>';

drawBottom();?>