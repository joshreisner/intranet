<?php
include("include.php");
drawTop();
echo drawTableStart();
if (url_id()) {
	//get a particular topic
	echo drawHeaderRow(db_grab("SELECT title FROM bb_topics_types WHERE id = " . $_GET["id"]), 4);
	$where = "t.type_id = " . $_GET["id"];
} else {
	echo drawHeaderRow("Uncategorised Topics", 4);
	$where = "t.type_id IS NULL";
}
$join = "";
if (getOption("channels") && $_SESSION["channel_id"]) {
	$where .= " AND t2c.channel_id = " . $_SESSION["channel_id"];
	$join = "JOIN bb_topics_to_channels t2c ON t.id = t2c.topic_id";
}

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
	$join
	WHERE t.is_active = 1 AND $where
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
		<tr class="thread<? if ($r["is_admin"] == 1) {?> admin<? }?>" onclick="location.href='topic.php?id=<?=$r["id"]?>';">
			<td class="input"><a href="topic.php?id=<?=$r["id"]?>"><?=$r["title"]?></a></td>
			<td><?=$r["firstname"]?> <?=$r["lastname"]?></td>
			<td align="center"><?=$r["replies"]?></td>
			<td align="right"><nobr><?=format_date($r["thread_date"])?></nobr></td>
		</tr>
	<? }
} else {
	echo drawEmptyResult("No topics have been added to this category yet.<br>Why not <a href='/bb/#bottom'>be the first</a>?", 4);
}
echo drawTableEnd();
drawBottom();
?>