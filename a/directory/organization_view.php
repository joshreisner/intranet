<?php
include("../../include.php");
echo drawTop();


if (isset($_GET["id"])) {
	$r = db_grab("SELECT 
			o.id,
			o.name, 
			o.address1, 
			o.address2,
			o.phone,
			o.hours,
			o.zip,
			o.updated_date,
			ISNULL(u.nickname, u.firstname) + ' ' + u.lastname updated_user
		FROM web_organizations o
		JOIN users u ON o.updated_user = u.id
		WHERE o.id = " . $_GET["id"]);
} else {
	$_GET["id"] = 0;
}

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Organization", 2, "edit", "organization_add_edit.php?id=" . $_GET["id"]);?>
	<form method="post" action="<?=$_josh["request"]["path_query"]?>">
	<tr>
		<td class="left">Name</td>
		<td><h1><?=$r["name"]?></h1></td>
	</tr>
	<tr>
		<td class="left">Address 1</td>
		<td><?=$r["address1"]?></td>
	</tr>
	<tr>
		<td class="left">Address 2</td>
		<td><?=$r["address2"]?></td>
	</tr>
	<tr>
		<td class="left">ZIP</td>
		<td><?=$r["zip"]?></td>
	</tr>
	<tr>
		<td class="left">Phone</td>
		<td><?=$r["phone"]?></td>
	</tr>
	<tr>
		<td class="left">Hours of Operation</td>
		<td><?=$r["hours"]?></td>
	</tr>
	<tr valign="top">
		<td class="left">Services</td>
		<td>
			<?
			$services = db_query("SELECT s.name 
						FROM web_services s 
						INNER JOIN web_organizations_2_services o2s ON o2s.serviceID = s.id
						WHERE o2s.organizationID = {$_GET["id"]} ORDER BY s.name
						");
			while ($s = db_fetch($services)) {?>
				<?=$s["name"]?><br>
			<? }?>
		</td>
	</tr>
	<tr valign="top">
		<td class="left">Languages (other than English)</td>
		<td>
			<?
			$languages = db_query("SELECT l.name
						FROM web_languages l
						INNER JOIN web_organizations_2_languages o2l ON o2l.languageID = l.id
						WHERE o2l.organizationID = {$_GET["id"]} ORDER BY l.name");
			while ($l = db_fetch($languages)) {
				if ($l["name"] == "English") continue;?>
				<?=$l["name"]?><br>
			<? }?>
		</td>
	</tr>
	<tr valign="top">
		<td class="left">Last Update</td>
		<td><?=format_date($r["updated_date"])?> by <?=$r["updated_user"]?></td>
	</tr>
	</form>
</table>
<?=drawBottom();?>