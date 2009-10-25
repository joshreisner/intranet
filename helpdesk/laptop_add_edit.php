<?php include("include.php");

if ($posting) {
	$laptopIsWireless = (isset($_POST["laptopIsWireless"])) ? 1 : 0;
	
	if (isset($_GET["id"])) {
		db_query("UPDATE IT_Laptops SET
			laptopName      = '" . $_POST["laptopName"] . "',
			laptopModel      = '" . $_POST["laptopModel"] . "',
			laptopHomeID      = " . $_POST["laptopHomeID"] . ",
			laptopSerial     = '" . $_POST["laptopSerial"] . "',
			laptopExpressServiceCode     = '" . $_POST["laptopExpressServiceCode"] . "',
			laptopServiceTag = '" . $_POST["laptopServiceTag"] . "',
			laptopOS         = '" . $_POST["laptopOS"] . "',
			laptopOffice     = '" . $_POST["laptopOffice"] . "',
			laptopIsWireless = $laptopIsWireless,
			laptopMACAddress    = '" . $_POST["laptopMACAddress"] . "',
			laptopPurpose    = '" . $_POST["laptopPurpose"] . "'
			WHERE laptopID = " . $_GET["id"]);
		db_query("DELETE FROM it_laptops_2_accessories WHERE laptopID = " . $_GET["id"]);
	} else { 
		$_GET["id"] = db_query("INSERT INTO IT_Laptops (
			laptopName,
			laptopModel,
			laptopHomeID,
			laptopSerial,
			laptopExpressServiceCode,
			laptopstatusID,
			laptopServiceTag,
			laptopOS,
			laptopOffice,
			laptopIsWireless,
			laptopMACAddress,
			laptopPurpose
		) VALUES (
			'" . $_POST["laptopName"] . "',			
			'" . $_POST["laptopModel"] . "',			
			" . $_POST["laptopHomeID"] . ",			
			'" . $_POST["laptopSerial"] . "',
			'" . $_POST["laptopExpressServiceCode"] . "',
			2,
			'" . $_POST["laptopServiceTag"] . "',			
			'" . $_POST["laptopOS"] . "',			
			'" . $_POST["laptopOffice"] . "',			
			$laptopIsWireless,			
			'" . $_POST["laptopMACAddress"] . "',
			'" . $_POST["laptopPurpose"] . "'
		)");
	}
	
	//accessories
	reset($_POST);
	while (list($key, $value) = each($_POST)) {
		@list($control, $accessoryID) = explode("_", $key);
		if ($control == "chkacc") {
			db_query("INSERT INTO it_laptops_2_accessories (
				laptopID,
				accessoryID
			) VALUES (
				" . $_GET["id"] . ",
				" . $accessoryID . "
			);");
		}
	}

	url_change("laptop.php?id=" . $_GET["id"]);
}

echo drawTop();

if (isset($_GET["id"])) {
	$r = db_grab("SELECT 
						l.laptopName,
						l.laptopPurpose,
						l.laptopModel,
						l.laptopHomeID,
						l.laptopSerial,
						l.laptopExpressServiceCode,
						l.laptopServiceTag,
						l.laptopOS,
						l.laptopOffice,
						l.laptopIsWireless,
						l.laptopMACAddress
					FROM IT_Laptops l
					WHERE laptopID = " . $_GET["id"]);

	$openEnded = (empty($r["laptopEnd"])) ? true : false;
}
?>

<a name="closedtickets"></a>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Add Laptop", 2);?>
	<form method="post" action="<?=$request["path_query"]?>">
	<tr>
		<td class="left">Laptop Name</td>
		<td><?=draw_form_text("laptopName", @$r["laptopName"])?></td>
	</tr>
	<tr>
		<td class="left">Model #</td>
		<td><?=draw_form_text("laptopModel", @$r["laptopModel"])?></td>
	</tr>
	<tr>
		<td class="left">Location</td>
		<td><?=draw_form_select("laptopHomeID", "SELECT id, name FROM it_laptops_homes", @$r["laptopHomeID"])?></td>
	</tr>
	<tr>
		<td class="left">Serial #</td>
		<td><?=draw_form_text("laptopSerial", @$r["laptopSerial"])?></td>
	</tr>
	<tr>
		<td class="left">Express Service Code</td>
		<td><?=draw_form_text("laptopExpressServiceCode", @$r["laptopExpressServiceCode"])?></td>
	</tr>
	<tr>
		<td class="left">Service Tag #</td>
		<td><?=draw_form_text("laptopServiceTag", @$r["laptopServiceTag"])?></td>
	</tr>
	<tr>
		<td class="left">Windows Version</td>
		<td><?=draw_form_text("laptopOS", @$r["laptopOS"])?></td>
	</tr>
	<tr>
		<td class="left">Office Version</td>
		<td><?=draw_form_text("laptopOffice", @$r["laptopOffice"])?></td>
	</tr>
	<tr>
		<td class="left">Wireless?</td>
		<td><?=draw_form_checkbox("laptopIsWireless", @$r["laptopIsWireless"])?> (check if yes)</td>
	</tr>
	<tr>
		<td class="left">MAC Address</td>
		<td><?=draw_form_text("laptopMACAddress", @$r["laptopMACAddress"])?></td>
	</tr>
	<tr>
		<td class="left">Accessories</td>
		<td>
			<table class="nospacing">
				<?
				if (isset($_GET["id"])) {
					$accessories = db_query("SELECT 
					a.id, 
					a.name,
					(SELECT COUNT(*) FROM it_laptops_2_accessories i2t WHERE i2t.accessoryID = a.id AND i2t.laptopID = {$_GET["id"]}) checked
					FROM it_laptops_accessories a ORDER BY a.name");
				} else {
					$accessories = db_query("SELECT 
					a.id, 
					a.name
					FROM it_laptops_accessories a ORDER BY a.name");
				}
				while ($a = db_fetch($accessories)) {?>
				<tr>
					<td><?=draw_form_checkbox("chkacc_" . $a["id"], @$a["checked"])?></td>
					<td><?=$a["name"]?></td>
				</tr>
				<?}?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="left">Notes</td>
		<td><?=draw_form_textarea("laptopPurpose", @$r["laptopPurpose"]);?></td>
	</tr>
	<tr>
		<td class="bottom" colspan="2">
			<?
			if (isset($_GET["id"])) {
				echo draw_form_submit("save changes");
			} else {
				echo draw_form_submit("add laptop");
			}
			?>
		</td>
	</tr>
	</form>
</table>
<?=drawBottom(); ?>