<?	include("include.php");

drawTop();

echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Tickets by Office", 3);?>
	<tr>
		<th align="left">Office</td>
		<th align="right" width="50">#</td>
		<th align="right" width="50">%</td>
	</tr>
	<? 
	$offices = db_query("SELECT 
		o.id,
		o.name,
		(SELECT COUNT(*) FROM helpdesk_tickets t JOIN intranet_users u ON t.createdBy = u.userID WHERE o.id = u.officeID $where) tickets,
		(SELECT SUM(timeSpent) FROM helpdesk_tickets t JOIN intranet_users u ON t.createdBy = u.userID WHERE o.id = u.officeID " . $where . ") minutes
		FROM intranet_offices o
		ORDER BY o.precedence");
	$counter = 0;
	while ($o = db_fetch($offices)) {
		if (!$o["tickets"] && $filtered) continue;
		$counter++;?>
		<tr class="helptext" bgcolor="#FFFFFF">
			<td><a href="office.php?id=<?=$o["id"]?><? if ($filtered) {?>&month=<?=$_GET["month"]?>&year=<?=$_GET["year"]?><? }?>"><?=$o["name"]?></a></td>
			<td align="right"><?=number_format($o["tickets"])?></a></td>
			<td align="right"><?=@round($o["minutes"] / $total["minutes"] * 100)?></td>
		</tr>
	<? }
	if (!$counter) {
		if ($filtered) {
			echo drawEmptyResult("No tickets have been posted with this office / month / year.", 3);
		} else {
			echo drawEmptyResult("No tickets have been posted from this office.", 3);
		}
	}
	?>
</table>
<? drawBottom();?>