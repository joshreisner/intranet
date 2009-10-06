<?
$clips = db_query('SELECT c.id, c.title FROM press_clips c ' . getChannelsWhere('press_clips', 'c', 'clip_id') . ' ORDER BY c.pub_date DESC', 4);
if (db_found($clips)) {
	while ($c = db_fetch($clips)) echo '<tr><td colspan="2"><a href="/press-clips/clip.php?id=' . $c['id'] . '">' . format_string($c['title'], 40) . '</a></td></tr>';
} else {
	echo '<tr><td colspan="2" class="empty">No clips have been added.</td></tr>';
}
?>