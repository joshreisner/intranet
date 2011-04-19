<?php include("include.php");

echo drawTop();

$r = db_grab("SELECT 
			l.laptopName,
			l.laptopPurpose,
			l.laptopModel,
			h.name laptopHome,
			l.laptopSerial,
			l.laptopExpressServiceCode,
			l.laptopServiceTag,
			l.laptopOS,
			l.laptopOffice,
			l.laptopIsWireless,
			l.laptopMACAddress,
			(SELECT count(*) FROM it_laptops_2_accessories l2a WHERE l2a.laptopID = l.laptopID) accessories
		FROM it_laptops l
		JOIN it_laptops_homes h ON l.laptopHomeID = h.id
		WHERE laptopID = " . $_GET["id"]);

$r["laptopIsWireless"] = ($r["laptopIsWireless"]) ? "Yes" : "No";

$openEnded = (empty($r["laptopEnd"])) ? true : false;

?>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("View Laptop", 2, "edit", "laptop_add_edit.php?id=" . $_GET["id"])?>
	<form method="post" action="<?=$request["path_query"]?>">
	<tr>
		<td class="left">Name</td>
		<td><b><?=$r["laptopName"]?></b></td>
	</tr>
	<tr>
		<td class="left"><nobr>Model #</nobr></td>
		<td><?=$r["laptopModel"]?></td>
	</tr>
	<tr>
		<td class="left"><nobr>Home</nobr></td>
		<td><?=$r["laptopHome"]?></td>
	</tr>
	<tr>
		<td class="left">Serial #</td>
		<td><?=$r["laptopSerial"]?></td>
	</tr>
	<tr>
		<td class="left">Express Service Code</td>
		<td><?=$r["laptopExpressServiceCode"]?></td>
	</tr>
	<tr>
		<td class="left">Service Tag #</td>
		<td><?=$r["laptopServiceTag"]?></td>
	</tr>
	<tr>
		<td class="left">OS</td>
		<td><?=$r["laptopOS"]?></td>
	</tr>
	<tr>
		<td class="left">Office Version</td>
		<td><?=$r["laptopOffice"]?></td>
	</tr>
	<tr>
		<td class="left">Is Wireless?</td>
		<td><?=$r["laptopIsWireless"]?></td>
	</tr>
	<tr>
		<td class="left">MAC Address</td>
		<td><?=$r["laptopMACAddress"]?></td>
	</tr>
	<? if ($r["accessories"]) {?>
	<tr>
		<td class="left">Accessories</td>
		<td>
		<?
		$accessories = db_query("SELECT a.name FROM it_laptops_accessories a JOIN it_laptops_2_accessories l2a ON a.id = l2a.accessoryID WHERE l2a.laptopID = " . $_GET["id"]);
		while ($a = db_fetch($accessories)) echo "&#183; " . $a["name"] . "<br>";?>
		</td>
	</tr>
	<? }?>
	<tr>
		<td class="left" height="120">Notes</td>
		<td><?=nl2br($r["laptopPurpose"])?></td>
	</tr>
	</form>
</table>

<table class="left" cellspacing="1">
	<tr>
		<td class="head helpdesk" colspan="4">History</td>
	</tr>
	<tr>
		<th align="left">User</th>
		<th align="left">Start</th>
		<th align="left">End</th>
		<th align="left">Notes</th>
	</tr>
	<?
	$result = db_query('SELECT
							ISNULL(u.nickname, u.firstname) first,
							u.lastname last,
							u.id,
							c.checkoutStart, 
							c.checkoutEnd,
							c.checkoutNotes,
							' . db_updated('u') . '
						FROM it_laptops_Checkouts c
						INNER JOIN users u ON c.checkoutUser = u.id
						WHERE checkoutLaptopID = " . $_GET["id"] . "
						ORDER BY checkoutStart DESC');
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><?=drawName($r["id"],$r["first"] . " " . $r["last"], false, true, $r['updated'])?></td>
		<td><?=format_date($r["checkoutStart"]);?></td>
		<td><?=format_date($r["checkoutEnd"]);?></td>
		<td><?=nl2br($r["checkoutNotes"])?></td>
	</tr>
	<? }?>
</table>

<?=drawBottom(); ?>