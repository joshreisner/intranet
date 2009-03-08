<?
$result = db_query("SELECT
		t.id,
		t.title,
		t.is_admin,
		t.threadDate,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topicID AND f.is_active = 1) replies,
		ISNULL(u.nickname, u.firstname) + ' ' + u.lastname name
	FROM bb_topics t
	JOIN users u ON u.user_id = t.created_user
	WHERE t.is_active = 1 
	ORDER BY t.threadDate DESC", 4);
while ($r = db_fetch($result)) { 
	if ($r["is_admin"]) $r["replies"] = "-";
	?>
	<tr height="20"<? if ($r["is_admin"]) {?> style="background-color:#fffce0;"<? }?>>
		<td width="90%"><a href="<?=$m["url"]?>topic.php?id=<?=$r["id"]?>"><?=format_text_shorten($r["title"], 41)?></a></td>
		<td width="10%" align="center"><?=$r["replies"]?></td>
	</tr>
<? }?>