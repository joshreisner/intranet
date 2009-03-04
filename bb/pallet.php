<?
$result = db_query("SELECT
		t.id,
		t.title,
		t.isAdmin,
		t.threadDate,
		(SELECT COUNT(*) FROM bulletin_board_followups f WHERE t.id = f.topicID AND f.isActive = 1) replies,
		ISNULL(u.nickname, u.firstname) + ' ' + u.lastname name
	FROM bulletin_board_topics t
	JOIN intranet_users u ON u.userID = t.createdBy
	WHERE t.isActive = 1 
	ORDER BY t.threadDate DESC", 4);
while ($r = db_fetch($result)) { 
	if ($r["isAdmin"]) $r["replies"] = "-";
	?>
	<tr height="20"<? if ($r["isAdmin"]) {?> style="background-color:#fffce0;"<? }?>>
		<td width="90%"><a href="<?=$module["url"]?>topic.php?id=<?=$r["id"]?>"><?=format_text_shorten($r["title"], 41)?></a></td>
		<td width="10%" align="center"><?=$r["replies"]?></td>
	</tr>
<? }?>