<?php include("include.php");

//jump to a ticket number
if (isset($_GET["id"])) {
	$_GET["id"] = trim($_GET["id"]) - 0;
	if ($_GET["id"]) {
		$result = db_query("SELECT id FROM helpdesk_tickets WHERE id = " . $_GET["id"]);
		if (db_found($result)) url_change("/helpdesk/ticket.php?id=" . $_GET["id"]);
	}
}

drawTop();

$month1 = $month;
$year1  = $year;
$month2 = ($month  == 1) ? 12 : $month  - 1;
$year2  = ($month  == 1) ? $year - 1 : $year;
$month3 = ($month2 == 1) ? 12 : $month2 - 1;
$year3  = ($month2 == 1) ? $year - 1 : $year2;
$month4 = ($month3 == 1) ? 12 : $month3 - 1;
$year4  = ($month3 == 1) ? $year - 1 : $year3;
$month5 = ($month3 == 1) ? 12 : $month4 - 1;
$year5  = ($month3 == 1) ? $year - 1 : $year4;

$month1amt = 0;
$month2amt = 0;
$month3amt = 0;
$month4amt = 0;
$month5amt = 0;


?>
<table class="message">
	<form method="get" action="/helpdesk/history.php" name="mainsearchform">
	<tr>
		<td class="gray">Jump to ticket # <input type="text" name="id" style="width:100px;"></td>
	</tr>
	</form>
</table>
<?
$result = db_query("SELECT 
						u.user_id,
						ISNULL(u.nickname, u.firstname) name,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.user_id AND MONTH(t.created_date) = $month1 AND YEAR(t.created_date) = $year1 AND t.departmentID = $departmentID) month1total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.user_id AND MONTH(t.created_date) = $month2 AND YEAR(t.created_date) = $year2 AND t.departmentID = $departmentID) month2total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.user_id AND MONTH(t.created_date) = $month3 AND YEAR(t.created_date) = $year3 AND t.departmentID = $departmentID) month3total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.user_id AND MONTH(t.created_date) = $month4 AND YEAR(t.created_date) = $year4 AND t.departmentID = $departmentID) month4total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.user_id AND MONTH(t.created_date) = $month5 AND YEAR(t.created_date) = $year5 AND t.departmentID = $departmentID) month5total
					FROM users_to_modules a 
					JOIN users u ON a.user_id = u.user_id
					WHERE a.module_id = 3 AND u.is_active = 1
					ORDER BY ISNULL(u.nickname, u.firstname)");
?>
<table class="left" cellspacing="1">
	<?= drawHeaderRow("History View", 6);?>
	<tr>
		<th align="left" width="25%">Name</td>
		<th align="right" width="15%"><?=substr($months[$month1 - 1], 0, 3)?> <?=$year1?></td>
		<th align="right" width="15%"><?=substr($months[$month2 - 1], 0, 3)?> <?=$year2?></td>
		<th align="right" width="15%"><?=substr($months[$month3 - 1], 0, 3)?> <?=$year3?></td>
		<th align="right" width="15%"><?=substr($months[$month4 - 1], 0, 3)?> <?=$year4?></td>
		<th align="right" width="15%"><?=substr($months[$month5 - 1], 0, 3)?> <?=$year5?></td>
	</tr>
	<? while ($r = db_fetch($result)) {?>
		<tr>
			<td><a href="administrator_tickets.php?user_id=<?=$r["user_id"]?>"><?=$r["name"]?></a></td>
			<td align="right"><?if ($r["month1total"]) {?><a href="administrator_tickets.php?user_id=<?=$r["user_id"]?>&month=<?=$month1?>&year=<?=$year1?>"><?}?><?=$r["month1total"]?></a></td>
			<td align="right"><?if ($r["month2total"]) {?><a href="administrator_tickets.php?user_id=<?=$r["user_id"]?>&month=<?=$month2?>&year=<?=$year2?>"><?}?><?=$r["month2total"]?></a></td>
			<td align="right"><?if ($r["month3total"]) {?><a href="administrator_tickets.php?user_id=<?=$r["user_id"]?>&month=<?=$month3?>&year=<?=$year3?>"><?}?><?=$r["month3total"]?></a></td>
			<td align="right"><?if ($r["month4total"]) {?><a href="administrator_tickets.php?user_id=<?=$r["user_id"]?>&month=<?=$month4?>&year=<?=$year4?>"><?}?><?=$r["month4total"]?></a></td>
			<td align="right"><?if ($r["month5total"]) {?><a href="administrator_tickets.php?user_id=<?=$r["user_id"]?>&month=<?=$month5?>&year=<?=$year5?>"><?}?><?=$r["month5total"]?></a></td>
		</tr>
		<?
		$month1amt += $r["month1total"];
		$month2amt += $r["month2total"];
		$month3amt += $r["month3total"];
		$month4amt += $r["month4total"];
		$month5amt += $r["month1total"];
	}?>
	<tr class="total">
		<td colspan="2"><?=$month1amt?></td>
		<td><?=$month2amt?></td>
		<td><?=$month3amt?></td>
		<td><?=$month4amt?></td>
		<td><?=$month5amt?></td>
	</tr>
</table>
<? drawBottom();?>