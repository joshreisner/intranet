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
						u.userID,
						ISNULL(u.nickname, u.firstname) name,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND MONTH(t.createdOn) = $month1 AND YEAR(t.createdOn) = $year1 AND t.departmentID = $departmentID) month1total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND MONTH(t.createdOn) = $month2 AND YEAR(t.createdOn) = $year2 AND t.departmentID = $departmentID) month2total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND MONTH(t.createdOn) = $month3 AND YEAR(t.createdOn) = $year3 AND t.departmentID = $departmentID) month3total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND MONTH(t.createdOn) = $month4 AND YEAR(t.createdOn) = $year4 AND t.departmentID = $departmentID) month4total,
						(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND MONTH(t.createdOn) = $month5 AND YEAR(t.createdOn) = $year5 AND t.departmentID = $departmentID) month5total
					FROM users_to_modules a 
					JOIN users u ON a.userID = u.userID
					WHERE a.moduleID = 3 AND u.isActive = 1
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
			<td><a href="administrator_tickets.php?userID=<?=$r["userID"]?>"><?=$r["name"]?></a></td>
			<td align="right"><?if ($r["month1total"]) {?><a href="administrator_tickets.php?userID=<?=$r["userID"]?>&month=<?=$month1?>&year=<?=$year1?>"><?}?><?=$r["month1total"]?></a></td>
			<td align="right"><?if ($r["month2total"]) {?><a href="administrator_tickets.php?userID=<?=$r["userID"]?>&month=<?=$month2?>&year=<?=$year2?>"><?}?><?=$r["month2total"]?></a></td>
			<td align="right"><?if ($r["month3total"]) {?><a href="administrator_tickets.php?userID=<?=$r["userID"]?>&month=<?=$month3?>&year=<?=$year3?>"><?}?><?=$r["month3total"]?></a></td>
			<td align="right"><?if ($r["month4total"]) {?><a href="administrator_tickets.php?userID=<?=$r["userID"]?>&month=<?=$month4?>&year=<?=$year4?>"><?}?><?=$r["month4total"]?></a></td>
			<td align="right"><?if ($r["month5total"]) {?><a href="administrator_tickets.php?userID=<?=$r["userID"]?>&month=<?=$month5?>&year=<?=$year5?>"><?}?><?=$r["month5total"]?></a></td>
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