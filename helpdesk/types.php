<?php include("include.php");

//adding a new type
if ($posting) {
	$_POST["departmentID"] = $departmentID;
	$id = db_save("helpdesk_tickets_types");
	url_change();
}

echo drawTop();

echo drawTicketFilter();
?>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Ticket Types", 3, "new type", "#bottom", "excel report", "types-report.php");?>
	<tr>
		<th>Ticket Type</th>
		<th class="r" width="50">#</th>
		<th class="r" width="50">%</th>
	</tr>
	<?
	$result = db_query("SELECT 
							y.id, 
							y.description,
							(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.type_id = y.id " . $where . ") tickets,
							(SELECT SUM(timeSpent) FROM helpdesk_tickets t WHERE t.type_id = y.id " . $where . ") minutes
						FROM helpdesk_tickets_types y
						WHERE y.departmentID = $departmentID
						ORDER BY y.description");
	$counter = 0;
	while ($r = db_fetch($result)) {
		if (!$r["tickets"] && $filtered) continue;
		$counter++;
		?>
	<tr>
		<td><a href="type.php?id=<?=$r["id"]?><? if ($filtered) {?>&month=<?=$_GET["month"]?>&year=<?=$_GET["year"]?><? }?>"><?=$r["description"]?></a></td>
		<td align="right"><?=number_format($r["tickets"])?></td>
		<td align="right"><?=@round($r["minutes"] / $total["minutes"] * 100)?></td>
	</tr>
	<? }
	$t = db_grab("SELECT COUNT(*) tickets, SUM(t.timeSpent) minutes FROM helpdesk_tickets t WHERE t.type_id IS NULL" . $where);
	if ($t["tickets"]) {
		$counter++;
	?>
	<tr>
		<td><a href="type.php<? if ($filtered) {?>?month=<?=$_GET["month"]?>&year=<?=$_GET["year"]?><? }?>"><i>No Type</i></a></td>
		<td align="right"><?=number_format($t["tickets"])?></td>
		<td align="right"><?=@round($t["minutes"] / $total["minutes"] * 100)?></td>
	</tr>
	<? }
	if (!$counter) {
		if ($filtered) {
			echo drawEmptyResult("No tickets for this month and year.", 3);
		} else {
			echo drawEmptyResult("No tickets.", 3);
		}
	}?>
</table>
<a name="bottom"></a>
<? 
$form = new intranet_form;
$form->addRow("itext", "Name" , "description", @$r["description"]);
$form->addRow("submit"  , "Add Type");
$form->draw("Add New Type");

echo drawBottom(); ?>