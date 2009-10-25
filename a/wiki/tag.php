<?php
include("../../include.php");
url_query_require("tags.php");

echo drawTop();

$r = db_grab("SELECT description FROM wiki_tags WHERE id = " . $_GET["id"]);
?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("<a href='tags.php' class='white'>Tags</a> &gt; " . $r["description"], 4);
	$topics = db_query("SELECT 
		w.id,
		w.title,
		w.description,
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		w.created_date
	FROM wiki_topics w
	JOIN wiki_topics_types t ON w.type_id = t.id
	JOIN users u ON w.created_user = u.id
	WHERE w.is_active = 1 AND 
		(SELECT COUNT(*) FROM wiki_topics_to_tags w2t WHERE w2t.topicID = w.id AND w2t.tagID = " . $_GET["id"] . ") > 0");
	if (db_found($topics)) {?>
	<tr>
		<th width="16"></th>
		<th align="left">Title</th>
		<th align="left" width="100">Created By</th>
		<th align="right" width="80">Created On</th>
	</tr>
	<?php
	while ($t = db_fetch($topics)) {?>
	<tr height="36">
		<td></td>
		<td><a href="topic.php?id=<?=$t["id"]?>"><?=$t["title"]?></a></td>
		<td><?=$t["first"]?> <?=$t["last"]?></td>
		<td align="right"><?=format_date($t["created_date"])?></td>
	</tr>
	<? }
	} else {
		echo drawEmptyResult("No Wiki Topics have been entered into the system with this tag yet", 4);
	}?>
</table>

<?=drawBottom(); ?>