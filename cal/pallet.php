<?
$events = db_query("SELECT
			e.id, 
			e.title, 
			e.start_date,
			t.color
		FROM cal_events e
		LEFT JOIN cal_events_types t ON e.type_id = t.id
		WHERE e.start_date > GETDATE() AND e.is_active = 1 
		ORDER BY e.start_date ASC", 4);
if (db_found($events)) {
	while ($e = db_fetch($events)) {?>
	<tr>
		<td width="80%"><a href="<?=$m["url"]?>event.php?id=<?=$e["id"]?>" class="block" style="background-color:<?=$e["color"]?>;"><?=$e["title"]?></a></td>
		<td width="20%" align="right"><?=format_date($e["start_date"], "", "M d")?></a></td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("No upcoming events.", 2);
}
