<?php include("include.php");

echo drawTop();

echo drawTicketFilter();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("All Active Users", 3);?>
	<tr>
		<th align="left">Name</td>
		<th align="right" width="50">#</td>
		<th align="right" width="50">%</td>
	</tr>
	<?
	$result = db_query("SELECT
							u.id,
							ISNULL(u.nickname, u.firstname) first,
							u.lastname last,
							(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.created_user = u.id $where) tickets,
							(SELECT SUM(timeSpent) FROM helpdesk_tickets t WHERE t.created_user = u.id " . $where . ") minutes
						FROM users u
						WHERE u.is_active = 1
						ORDER BY last, first");
	$counter = 0;
	while ($r = db_fetch($result)) {
		if (!$r["tickets"] && $filtered) continue;
		$counter++;
	?>
	<tr>
		<td><a href="user.php?id=<?=$r["id"]?><? if ($filtered) {?>&month=<?=$_GET["month"]?>&year=<?=$_GET["year"]?><? }?>"><?=$r["first"]?> <?=$r["last"]?></a></td>
		<td align="right"><?=number_format($r["tickets"])?></td>
		<td align="right"><?=@round($r["minutes"] / $total["minutes"] * 100)?></td>
	</tr>
	<? }
	if (!$counter) {
		if ($filtered) {
			echo drawEmptyResult("No tickets in this month / year.", 3);
		} else {
			echo drawEmptyResult("No tickets.", 3);
		}
	}
	?>
</table>
<?=drawBottom(); ?>