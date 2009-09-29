<?
include("../include.php");

if ($posting) {
	if (isset($_GET["id"])) {
		db_query("UPDATE web_organizations SET
						name = '{$_POST["name"]}',
						address1 = '{$_POST["address1"]}',
						address2 = '{$_POST["address2"]}',
						phone = '{$_POST["phone"]}',
						hours = '{$_POST["hours"]}',
						zip = '{$_POST["zip"]}',
						updated_date = GETDATE(),
						updated_user = {$_SESSION["user_id"]}
				  WHERE id = " . $_GET["id"]);
		db_query("DELETE FROM web_organizations_2_services  WHERE organizationID = " . $_GET["id"]);
		db_query("DELETE FROM web_organizations_2_languages WHERE organizationID = " . $_GET["id"]);
	} else {
		db_query("INSERT into web_organizations (
						name,
						address1,
						address2,
						phone,
						hours,
						zip,
						updated_date,
						updated_user
					) VALUES (
						'{$_POST["name"]}',
						'{$_POST["address1"]}',
						'{$_POST["address2"]}',
						'{$_POST["phone"]}',
						'{$_POST["hours"]}',
						'{$_POST["zip"]}',
						GETDATE(),
						{$_SESSION["user_id"]}
					)");
		$_GET["id"] = db_grab("SELECT MAX(id) FROM web_organizations");
	}
	
	reset($_POST);
	while (list($key, $value) = each($_POST)) {
		@list($control, $organizationID, $serviceID) = explode("_", $key);
		if ($control == "chks") {
			if ($organizationID == 0) $organizationID = $_GET["id"];
			db_query("INSERT INTO web_organizations_2_services ( organizationID, serviceID ) VALUES ( $organizationID, $serviceID )");
		}
	}
	
	reset($_POST);
	while (list($key, $value) = each($_POST)) {
		@list($control, $organizationID, $languageID) = explode("_", $key);
		if ($control == "chkl") {
			if ($organizationID == 0) $organizationID = $_GET["id"];
			db_query("INSERT INTO web_organizations_2_languages ( organizationID, languageID ) VALUES ( $organizationID, $languageID )");
		}
	}
	url_change("organization_view.php?id=" . $_GET["id"]);
}

drawTop();

if (isset($_GET["id"])) {
	$r = db_grab("SELECT 
			o.id,
			o.name, 
			o.address1, 
			o.address2,
			o.phone,
			o.hours,
			o.zip
		FROM web_organizations o
		WHERE o.id = " . $_GET["id"]);
} else {
	$_GET["id"] = 0;
}

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Edit Organization", 2);?>
	<form method="post" action="<?=$_josh["request"]["path_query"]?>">
	<tr>
		<td width="20%" class="left">Name</td>
		<td><?=draw_form_text("name", @$r["name"])?></td>
	</tr>
	<tr>
		<td class="left">Address 1</td>
		<td><?=draw_form_text("address1", @$r["address1"])?></td>
	</tr>
	<tr>
		<td class="left">Address 2</td>
		<td><?=draw_form_text("address2", @$r["address2"])?></td>
	</tr>
	<tr>
		<td class="left">ZIP</td>
		<td><?=draw_form_text("zip", @$r["zip"], 5, 5)?></td>
	</tr>
	<tr>
		<td class="left">Phone</td>
		<td><?=draw_form_text("phone", @$r["phone"], 14, 14)?></td>
	</tr>
	<tr>
		<td class="left">Hours of Operation</td>
		<td><?=draw_form_text("hours", @$r["hours"])?></td>
	</tr>
	<tr valign="top">
		<td class="left">Services</td>
		<td>
		<table class="nospacing">
			<?
			$services = db_query("SELECT s.id, s.name, 
						(SELECT count(*) FROM web_organizations_2_services o2s WHERE o2s.organizationID = {$_GET["id"]} AND o2s.serviceID = s.id) as checked
						FROM web_services s ORDER BY s.name");
			while ($s = db_fetch($services)) {?>
			<tr>
				<td width="25"><?=draw_form_checkbox("chks_" . $_GET["id"] . "_" . $s["id"], $s["checked"])?></td>
				<td><?=$s["name"]?></td>
			</tr>
			<?}?>
			</table>
		</td>
	</tr>
	<tr valign="top">
		<td class="left">Languages (other than english):</td>
		<td>
		<table class="nospacing">
			<?
			$languages = db_query("SELECT l.id, l.name, 
						(SELECT count(*) FROM web_organizations_2_languages o2l WHERE o2l.organizationID = {$_GET["id"]} AND o2l.languageID = l.id) as checked
						FROM web_languages l ORDER BY l.name");
			while ($l = db_fetch($languages)) {
				if ($l["name"] == "English") continue;?>
			<tr>
				<td width="25"><?=draw_form_checkbox("chkl_" . $_GET["id"] . "_" . $l["id"], $l["checked"])?></td>
				<td><?=$l["name"]?></td>
			</tr>
			<?}?>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan="2" align="center" class="left">
			<? if ($_GET["id"]) {
				echo draw_form_submit("save changes");
			} else {
				echo draw_form_submit("add organization");
			}?></td>
	</tr>
	</form>
</table>
<? drawBottom();?>