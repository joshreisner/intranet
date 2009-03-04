<? include("../include.php");

if ($posting) {
	$_POST["description"] = format_html($_POST["message"]);
	$_POST["topicID"] = $_GET["id"];
	$id = db_enter("bulletin_board_followups", "topicID |description");
	db_grab("SELECT topicID FROM bulletin_board_followups WHERE id = " . $id);
	db_query("UPDATE bulletin_board_topics SET threadDate = GETDATE() WHERE id = " .  $_GET["id"]);
	syndicateBulletinBoard();
	url_change();
}

//set topic and followups to deleted
if (isset($_GET["delete"])) {
	db_query("UPDATE bulletin_board_topics SET 
				isActive = 0,
				deletedOn = GETDATE(),
				deletedBy = {$_SESSION["user_id"]}
			  WHERE id = " . $_GET["id"]);
	syndicateBulletinBoard();
	url_change("/bb/");
} elseif (isset($_GET["deleteFollowupID"])) {
	db_query("UPDATE bulletin_board_followups SET 
				isActive = 0,
				deletedOn = GETDATE(),
				deletedBy = {$_SESSION["user_id"]}
			  WHERE ID = " . $_GET["deleteFollowupID"]);
	url_query_drop("deleteFollowupID");
}

//get topic data
$r = db_grab("SELECT 
		t.title,
		t.description,
		t.createdOn,
		t.isAdmin,
		u.userID,
		u.imageID,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname,
		i.width,
		i.height
		FROM bulletin_board_topics t
		JOIN intranet_users u ON t.createdBy = u.userID
		LEFT JOIN intranet_images i ON u.imageID = i.imageID
		WHERE t.id = " . $_GET["id"]);

//check that it exists
	if (empty($r)) url_change("/bb/");

drawTop();
echo drawSyndicateLink("bb");

$isPoster = ($r["userID"] == $_SESSION["user_id"]) ? true : false;


$r["description"] = htmlwrap($r["description"]);

//if ($_GET["id"] == 7966) echo drawServerMessage("<b>Note</b>: This comments on this post are organized in reverse-chronological order.");

if ($r["isAdmin"]) echo drawServerMessage("<b>Note</b>: This is an Administration/Human Resources topic.  For more information, please contact the <a href='mailto:hrpayroll@seedco.org'>Human Resources Department</a>.");
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
	if ($isAdmin || $isPoster) {
		echo drawHeaderRow($r["title"], 2, "edit", "edit.php?id=" . $_GET["id"], "delete", "javascript:checkDelete();");
	} else {
		if ($r["isAdmin"]) {
			echo drawHeaderRow($r["title"], 2);
		} else {
			echo drawHeaderRow($r["title"], 2, "add a followup", "#bottom");
		}
	}
	echo drawThreadTop($r["title"], $r["description"], $r["userID"], $r["firstname"] . " " . $r["lastname"], $r["imageID"], $r["width"], $r["height"], $r["createdOn"]);
	//get replies
	//$direction = ($_GET["id"] == 7966) ? "DESC" : "ASC";
	if (!$r["isAdmin"]) {
		$followups = db_query("SELECT
					f.id,
					f.description,
					u.userID,
					ISNULL(u.nickname, u.firstname) firstname,
					u.lastname,
					f.createdOn as postedDate,
					f.createdBy as userID,
					i.imageID,
					i.width,
					i.height
				FROM bulletin_board_followups f
				JOIN intranet_users u ON u.userID = f.createdBy
				LEFT JOIN intranet_images i ON u.imageID = i.imageID
				WHERE f.isActive = 1 AND f.topicID = {$_GET["id"]}
				ORDER BY f.createdOn");
		while ($f = db_fetch($followups)) { 
			echo drawThreadComment($f["description"], $f["userID"], $f["firstname"] . " " . $f["lastname"], $f["imageID"], $f["width"], $f["height"], $f["postedDate"]);
		}
		echo drawThreadCommentForm(false);
	}
echo '</table>';

drawBottom();?>