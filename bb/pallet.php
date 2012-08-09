<?php
$result = db_query('SELECT
		id,
		title' . langExt() . ' title,
		is_admin,
		replies
	FROM bb_topics t
	' . getChannelsWhere('bb_topics', 't', 'topic_id') . '
	ORDER BY thread_date DESC', 4);
if (db_found($result)) {
	while ($r = db_fetch($result)) {
		$return .= draw_container('tr', 
			'<td width="90%"><a href="/' . $m["folder"] . '/topic.php?id=' . $r["id"] . '">' . format_string($r["title"], 39) . '</a></td>
			<td width="10%" align="center">' . $r["replies"] . '</td>',
			(($r["is_admin"] == 1) ? array('class'=>'admin') : false)
		);
	}
} else {
	$return .= drawEmptyResult("No topics added yet.", 2);
}
