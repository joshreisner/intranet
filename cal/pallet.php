<?
$events = db_query("SELECT
			e.id, 
			e.title, 
			e.startDate,
			t.color
		FROM cal_events e
		LEFT JOIN cal_events_types t ON e.typeID = t.id
		WHERE e.startDate > GETDATE() AND e.is_active = 1 
		ORDER BY e.startDate ASC", 4);
if (db_found($events)) {
	while ($e = db_fetch($events)) {?>
	<tr>
		<td width="70%"><a href="<?=$m["url"]?>event.php?id=<?=$e["id"]?>" class="block" style="background-color:<?=$e["color"]?>;"><?=$e["title"]?></a></td>
		<td width="30%" align="right"><?=format_date($e["startDate"])?></a></td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("No upcoming events.", 2);
}
