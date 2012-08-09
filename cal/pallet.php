<?php
$events = db_query('SELECT
			e.id, 
			e.title' . langExt() . ' title, 
			e.start_date,
			t.color
		FROM cal_events e
		LEFT JOIN cal_events_types t ON e.type_id = t.id
		' . getChannelsWhere('cal_events', 'e', 'event_id') . ' AND e.start_date > ' . db_date() . '
		ORDER BY e.start_date ASC', 4);
if (db_found($events)) {
	while ($e = db_fetch($events)) {
		$return .= '
	<tr>
		<td width="80%"><a href="/' . $m['folder'] . '/event.php?id=' . $e['id'] . '" class="block" style="background-color:' . $e['color'] . ';">' . $e['title'] . '</a></td>
		<td width="20%" align="right">' . format_date($e['start_date'], "", "%b %d") . '</td>
	</tr>';
	}
} else {
	$return .= '<tr><td colspan="2" class="empty">' . getString('events_empty') . '</td></tr>';
}