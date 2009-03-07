<?
$events = db_query("SELECT
			e.id, 
			e.title, 
			e.startDate,
			t.color
		FROM cal_events e
		JOIN cal_events_types t ON e.typeID = t.id
		WHERE e.startDate > GETDATE() AND e.isActive = 1 
		ORDER BY e.startDate ASC", 4);
while ($e = db_fetch($events)) {?>
<tr>
	<td width="70%"><a href="<?=$module["url"]?>event.php?id=<?=$e["id"]?>" class="block" style="background-color:<?=$e["color"]?>;"><?=$e["title"]?></a></td>
	<td width="30%" align="right"><?=format_date($e["startDate"])?></a></td>
</tr>
<? }?>