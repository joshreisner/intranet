<?
$clips = db_query('SELECT c.id, c.title' . langExt() . ' title FROM press_clips c ' . getChannelsWhere('press_clips', 'c', 'clip_id') . ' ORDER BY c.pub_date DESC', 4);
if (db_found($clips)) {
	while ($c = db_fetch($clips)) $return .= '<tr><td colspan="2"><a href="/' . $m['folder'] . '/clip.php?id=' . $c['id'] . '">' . format_string($c['title'], 40) . '</a></td></tr>';
} else {
	$return .= '<tr><td colspan="2" class="empty">No clips have been added.</td></tr>';
}
?>