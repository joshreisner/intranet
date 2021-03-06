<?php include("include.php");

//deactivate laptop
if (isset($_GET["deactivate"])) {
	db_query("UPDATE it_laptops SET is_active = 0 WHERE laptopID = " . $_GET["deactivate"]);
	url_drop();
}

//handle laptop checkin
if (isset($_GET["checkin"])) {
	db_query("UPDATE it_laptops SET checkoutID = NULL, laptopStatusID = 2 WHERE laptopID = " . $_GET["checkin"]);
	url_drop();
}

echo drawTop();

if ($_SESSION["departmentID"] != 8) {
	echo drawMessage("This page is specific to IT.");
	drawBottom();
	exit;
}

?>
<table class="left" cellspacing="1">
	<?
	echo drawHeaderRow("Laptop Requests", 5);
	$result = db_query('SELECT
						t.title,
						t.statusID,
						(SELECT count(*) FROM helpdesk_tickets_followups f WHERE f.ticketID = t.id) ticketfollowups,
						t.created_user,
						t.updated_date,
						' . db_updated('t') . ',
						t.id,
						t.ownerID,
						t.priorityID,
						t.created_date,
						ISNULL(u.nickname, u.firstname) first,
						u.lastname last,
						(SELECT COUNT(*) FROM users_to_modules a WHERE a.module_id = 3 AND a.user_id = t.created_user) is_adminIT
					FROM helpdesk_tickets t
					JOIN users u ON u.id    = t.created_user
					WHERE t.statusID <> 9 AND t.type_id = 1
					ORDER BY t.created_date DESC');
if (db_found($result)) {?>
	<tr>
		<th>User</th>
		<th>Priority</th>
		<th>Status</th>
		<th>Assigned To</th>
		<th width="16"></th>
	</tr>
	<? while ($r = db_fetch($result)) echo drawTicketRow($r);
} else {
	echo drawEmptyResult("There are no open laptop requests right now!", 5);
}?>
</table>

<script language="javascript">
	<!--
	function validate(form) {
		if (!form.laptopName.value.length) {
			alert("Please enter a laptop name.");
			return false;
		}
		return true;
	}

	function deactivateLaptop(id,name) {
		if(confirm("Are you sure you want to deactivate the laptop " + name + "?")) location.href="<?=$request["path_query"]?>?deactivate=" + id;
	}
	//-->
</script>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Laptops", 7, "add a laptop", "laptop_add_edit.php");?>
	<tr>
		<th align="left">Name</th>
		<th>Status</th>
		<th align="left">User</th>
		<th align="right">Start</th>
		<th align="right">End</th>
		<th></th>
		<th></th>
	</tr>
	<?
	$homes = db_query("SELECT id, name, (SELECT count(*) FROM it_laptops l WHERE l.laptopHomeID = h.id) countlaptops FROM it_laptops_homes h");
	while ($h = db_fetch($homes)) {
		if ($h["countlaptops"]) {?>
	<tr class="group">
		<td colspan="7"><b><?=$h["name"]?></b></td>
	</tr>
	<? $result = db_query("SELECT 
			l.laptopID,
			l.laptopName,
			l.laptopIsWireless as isWireless,
			s.laptopStatusDesc,
			lc.checkoutStart,
			lc.checkoutEnd,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last
		FROM it_laptops l
		INNER JOIN it_laptops_statuses s ON s.laptopStatusID = l.laptopStatusID
		LEFT JOIN it_laptops_checkouts lc ON l.checkoutID = lc.checkoutID
		LEFT JOIN users u ON u.id = lc.checkoutUser
		WHERE l.laptopHomeID = {$h["id"]} AND l.is_active = 1
		ORDER BY laptopName");
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="laptop.php?id=<?=$r["laptopID"]?>"><?=$r["laptopName"]?></a></td>
		<td><?=$r["laptopStatusDesc"]?></td>
		<td><?=$r["first"]?> <?=$r["last"]?></td>
		<td align="right"><?=format_date($r["checkoutStart"], "")?></td>
		<td align="right"><?=format_date($r["checkoutEnd"], "")?></td>
		<td align="center"><nobr><? if ($r["laptopStatusDesc"] == "In") {?>
			<a href="laptop_checkout.php?id=<?=$r["laptopID"]?>">check out</a>
			<? } else { ?>
			<a href="<?=url_query_add(array("checkin"=>$r["laptopID"]), false)?>">check in</a>
			<? }?></nobr></td>
		<td width="16"><a href="javascript:deactivateLaptop(<?=$r["laptopID"]?>,'<?=$r["laptopName"]?>');"><img src="/images/icons/delete.png" width="16" height="16" border="0"></a></td>
	</tr>
			<? }
		}
	}?>
</table>
<?=drawBottom(); ?>