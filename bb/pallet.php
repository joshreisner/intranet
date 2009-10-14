<?
$result = db_query('SELECT
		t.id,
		t.title' . langExt() . ' title,
		t.is_admin,
		t.thread_date,
		(SELECT COUNT(*) FROM bb_followups f WHERE t.id = f.topic_id AND f.is_active = 1) replies,
		ISNULL(u.nickname, u.firstname) + " " + u.lastname name
	FROM bb_topics t
	JOIN users u ON u.id = t.created_user
	' . getChannelsWhere('bb_topics', 't', 'topic_id') . '
	ORDER BY t.thread_date DESC', 4);
if (db_found($result)) {
	while ($r = db_fetch($result)) { ?>
		<tr height="20"<? if ($r["is_admin"] == 1) {?> class="admin"<? }?>>
			<td width="90%"><a href="<?=$m["url"]?>topic.php?id=<?=$r["id"]?>"><?=format_string($r["title"], 39)?></a></td>
			<td width="10%" align="center"><?=$r["replies"]?></td>
		</tr>
	<? }
} else {
	echo drawEmptyResult("No topics added yet.", 2);
}
