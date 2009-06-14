<?php
include("include.php");

if ($posting) {
	error_debug("handling bb post");
	format_post_bits("is_admin");
	$id = db_save("bb_topics");
	db_query("UPDATE bb_topics SET thread_date = GETDATE() WHERE id = " . $id);
	if (getOption("channels")) db_checkboxes("channels", "bb_topics_to_channels", "topic_id", "channel_id", $id);
	
	//notification
	if ($_POST["is_admin"] == "1") {
		//get addresses of everyone & send with message
		emailUsers(db_array("SELECT email FROM users WHERE is_active = 1"), $_POST["title"], bbDrawTopic($id), 2, getString("bb_admin"));
	} elseif (getOption("bb_notifypost")) {
		//get addresses of everyone with notify_topics checked and send
		emailUsers(db_array("SELECT email FROM users WHERE is_active = 1 AND notify_topics = 1"), $_POST["title"], bbDrawTopic($id), 2);
	}
	
	bbDrawRss();
	url_change();
}

drawTop();
echo draw_autorefresh(5);
echo drawSyndicateLink("bb");
echo drawTableStart();
echo drawHeaderRow("", 4, "new", "#bottom");
error_debug("get bb topix");

$where = "WHERE t.is_active = 1";
if (getOption("channels") && $_SESSION["channel_id"]) $where = "JOIN bb_topics_to_channels t2c ON t.id = t2c.topic_id WHERE t.is_active = 1 AND t2c.channel_id = " . $_SESSION["channel_id"];
$topics = db_query("SELECT 
		t.id,
		t.title,
		t.is_admin,
		t.thread_date,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topic_id AND f.is_active = 1) replies,
		ISNULL(u.nickname, u.firstname) firstname,
		u.lastname
	FROM bb_topics t
	JOIN users u ON u.id = t.created_user
	$where
	ORDER BY t.thread_date DESC", 15);
if (db_found($topics)) {?>
	<tr>
		<th width="320">Topic</th>
		<th width="120">Starter</th>
		<th class="c">Replies</th>
		<th class="r">Last Post</th>
	</tr>
	<?
	while ($r = db_fetch($topics)) {
		$r["lastname"] = htmlentities($r["lastname"]); //see http://work.joshreisner.com/request/?id=477
		?>
		<tr class="thread<? if ($r["is_admin"] == 1) {?> admin<? }?>"
			onclick		= "location.href='topic.php?id=<?=$r["id"]?>';"
			onmouseover	= "javascript:aOver('id<?=$r["id"]?>')"
			onmouseout	= "javascript:aOut('id<?=$r["id"]?>')">
			<td class="input"><a href="topic.php?id=<?=$r["id"]?>" id="id<?=$r["id"]?>"><?=$r["title"]?></a></td>
			<td><?=$r["firstname"]?> <?=$r["lastname"]?></td>
			<td align="center"><?=$r["replies"]?></td>
			<td align="right"><nobr><?=format_date($r["thread_date"])?></nobr></td>
		</tr>
	<? }
} else {
	echo drawEmptyResult("No topics have been added yet.  Why not <a href='#bottom'>be the first</a>?", 4);
}

?>
</table>
<a name="bottom"></a>
<?
$form = new intranet_form;
if ($module_admin) {
	$form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, true);
	$form->addCheckbox("is_admin",  "Admin Post?", 0, "(check if yes)", true);
}
$form->addRow("itext",  "Subject" , "title", "", "", true);
if (getOption("bb_types")) $form->addRow("select",  "Category" , "type_id", "SELECT id, title FROM bb_topics_types");
if (getOption("channels")) $form->addCheckboxes("channels", "Networks", "channels", "bb_topics_to_channels", "topic_id", "channel_id");
$form->addRow("textarea", "Message" , "description", "", "", true);
$form->addRow("submit"  , "add new topic");
$form->draw("Contribute a New Topic");

drawBottom(); 
?>