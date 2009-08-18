<?php
include("include.php");

drawTop();
echo drawSyndicateLink("bb");
echo drawTableStart();
echo drawHeaderRow("", 4, "new", "#bottom");

//get bulletin board topics
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
	WHERE t.is_active = 1 
	ORDER BY t.thread_date DESC");
if (db_found($topics)) {?>
	<tr>
		<th width="320">Topic</th>
		<th width="120">Starter</th>
		<th class="c">Replies</th>
		<th class="r">Last Post</th>
	</tr>
	<?
	while ($r = db_fetch($topics)) {?>
		<tr class="thread<? if ($r["is_admin"] == 1) {?> admin<? }?>"
				onclick		= "location.href='topic.php?id=<?=$r["id"]?>';"
				onmouseover	= "javascript:aOver('id<?=$r["id"]?>')"
				onmouseout	= "javascript:aOut('id<?=$r["id"]?>')">
			<td class="input"><a href="topic.php?id=<?=$r["id"]?>" id="id<?=$r["id"]?>"><?=$r["title"]?></a></td>
			<td><?=$r["firstname"]?> <?=$r["lastname"]?></td>
			<td align="center"><?=$r["replies"]?></td>
			<td align="right"><nobr><?=format_date($r["thread_date"])?></nobr></td>
		</tr>
		<? 
	}
} else {
	echo drawEmptyResult("No topics have been added yet.  Why not <a href='#bottom'>be the first</a>?", 4);
}?>
</table>
<?=drawBottom();?>