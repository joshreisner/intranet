<?php
include("../../include.php");

if (isset($_GET["deleteID"])) { //delete topic
	db_query("UPDATE wiki_topics SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE() WHERE id = " . $_GET["deleteID"]);
	url_change("./");
}

url_query_require();

if ($uploading) { //upload an attachment
	list($_POST["type_id"], $_POST["content"]) = file_get_uploaded("userfile", "docs_types");
	$_POST["topicID"] = $_GET["id"];
	$id = db_save("wiki_topics_attachments"], false);
	url_change();
} elseif ($posting) { //add a comment
	$_POST["description"] = $_POST["message"];
	$_POST["topicID"] = $_GET["id"];
	$id = db_save("wiki_topics_comments");
	url_change();
}

echo drawTop();

//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query("SELECT description, extension FROM docs_types ORDER BY description");
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t["extension"] . '")';
	$doctypes[] = " - " . $t["description"] . " (." . $t["extension"] . ")";
}

$t = db_grab("SELECT 
		w.title,
		w.description,
		w.type_id,
		(SELECT COUNT(*) FROM wiki_topics_attachments a WHERE a.topicID = w.id) hasAttachments,
		t.description type,
		w.is_active,
		w.created_date,
		w.created_user,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last
	FROM wiki_topics w
	JOIN wiki_topics_types t ON w.type_id = t.id
	JOIN users u ON w.created_user = u.id
	WHERE w.id = " . $_GET["id"]);
?>
<script language="javascript">
	<!--
	function validate(form) {
		if (!form.title.value.length) {
			alert("Please enter a name for the attachment.");
			return false;
		}
		if (!form.userfile.value.length) {
			alert("Please select a file to upload.");
			return false;
		} else {
			var arrFile   = form.userfile.value.split(".");
			var extension = arrFile[arrFile.length - 1].toLowerCase();
			if (<?=implode(" && ", $extensions)?>) {
				alert("Only these filetypes are supported by this system:\n\n <?=implode("\\n", $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filename.");
				return false;
			}
		}
		
		return true;
	}
	function validateComment(form) {
		if (!form.description.value.length || (form.description.value == '<p>&nbsp;</p>')) return false;
		return true;
	}
	//-->
</script>
	<?
	echo drawTableStart();
	if ($page['is_admin']) {
		echo drawHeaderRow("View Topic", 2, "edit", "topic_edit.php?id=" . $_GET["id"], "delete", "topic_edit.php?deleteID=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Topic", 2);
	}?>
	<tr>
		<td class="left">Type</td>
		<td><a href="type.php?id=<?=$t["type_id"]?>"><?=$t["type"]?></a></td>
	</tr>
	<tr>
		<td class="left">Tags</td>
		<td>
		<?php
		$result = db_query("SELECT 
				t.id,
				t.description
			FROM wiki_tags t
			WHERE (SELECT COUNT(*) FROM wiki_topics_to_tags w2t WHERE w2t.topicID = " . $_GET["id"] . " AND w2t.tagID = t.id) > 0
			ORDER BY t.description");
		if (db_found($result)) {
			while ($r = db_fetch($result)) {
				$tags[] = '<a href="tag.php?id=' . $r["id"] . '">' . $r["description"] . '</a>';
			}
			echo implode(", ", $tags);
		} else {
			echo "<i>untagged</i>";
		}?>
		</td>
	</tr>
	<? if ($t["hasAttachments"]) {?>
	<tr>
		<td class="left">Attachments</td>
		<td>
		<table class="nospacing">
		<?
				$attachments = db_query("SELECT
				a.id,
				a.title,
				t.icon,
				t.description type
			FROM wiki_topics_attachments a
			JOIN docs_types t ON a.type_id = t.id
			WHERE a.topicID = " . $_GET["id"]);
		while ($a = db_fetch($attachments)) {?>
			<tr height="21">
				<td width="18"><a href="download.php?id=<?=$a["id"]?>"><img src="<?=$_josh["write_folder"]?>/<?=$a["icon"]?>" width="16" height="16" border="0"></a></td>
				<td><a href="download.php?id=<?=$a["id"]?>"><?=$a["title"]?></a></td>
			</tr>
		<? } ?>
		</table>
		</td>
	</tr>
	<? } 
	echo drawThreadTop($t["title"], $t["description"], $t["created_user"], $t["first"] . " " . $t["last"], $t["created_date"]);
		$comments = db_query("SELECT 
				c.id, 
				c.description,
				c.created_date,
				c.created_user,
				ISNULL(u.nickname, u.firstname) first,
				u.lastname last
			FROM wiki_topics_comments c
			JOIN users u ON c.created_user = u.id
			WHERE c.topicID = {$_GET["id"]}
			ORDER BY c.created_date ASC");
		while ($c = db_fetch($comments)) {
			echo drawThreadComment($c["description"], $c["created_user"], $c["first"] . " " . $c["last"], $c["created_date"]);
		}
		echo drawThreadCommentForm();
	echo drawTableEnd();

if ($page['is_admin']) {?>
<table class="left">
	<?=drawHeaderRow("Attach Document", 2);?>
	<form enctype="multipart/form-data" action="<?=$_josh["request"]["path_query"]?>" method="post" onsubmit="javascript:return validateComment(this);">
	<tr>
		<td class="left">Document Name</td>
		<td><?=draw_form_text("title",  @$d["name"])?></td>
	</tr>
	<tr>
		<td class="left">File</td>
		<td><input type="file" name="userfile" size="40" class="field" value=""></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?=draw_form_submit("Attach Document");?></td>
	</tr>
	</form>
</table>
<? }
echo drawBottom();?>