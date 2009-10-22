<?
include("../../include.php");
drawTop();

$r = db_grab("SELECT 
		j.id,
		j.title,
		j.description,
		c.description corporationName,
		o.name office,
		j.created_date, 
		j.updated_date,
		j.deleted_date,
		u1.firstname created_userFirst,
		u1.lastname created_userLast,
		u2.firstname updated_userFirst,
		u2.lastname updated_userLast,
		u3.firstname deleted_userFirst,
		u3.lastname deleted_userLast
	FROM openings j
	LEFT JOIN organizations c ON j.corporationID = c.id
	LEFT JOIN offices o ON j.officeID = o.id
	LEFT JOIN users u1 ON j.created_user = u1.id
	LEFT JOIN users u2 ON j.updated_user = u2.id
	LEFT JOIN users u3 ON j.deleted_user = u3.id
	
	WHERE j.id = " . $_GET["id"]);
	$r["created_user"] = ($r["created_userFirst"]) ? $r["created_userFirst"] . " " . $r["created_userLast"] : false;
	$r["updated_user"] = ($r["updated_userFirst"]) ? $r["updated_userFirst"] . " " . $r["updated_userLast"] : false;
	$r["deleted_user"] = ($r["deleted_userFirst"]) ? $r["deleted_userFirst"] . " " . $r["deleted_userLast"] : false;
?>
<table class="left" cellspacing="1">
	<? if ($page['is_admin']) {
		echo drawHeaderRow("View Position", 2, "edit", "position_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Position", 2);
	}?>
	<tr>
		<td class="left">Organization</td>
		<td><?=$r["corporationName"]?></td>
	</tr>
	<tr>
		<td class="left">Location</td>
		<td><?=$r["office"]?></td>
	</tr>
	<tr>
		<td class="left">Position</td>
		<td class="text">
			<h1><?=$r["title"]?></h1>
			<?=$r["description"]?>
		</td>
	</tr>
	<? if ($r["created_date"]) {?>
	<tr>
		<td class="left">Posted</td>
		<td><?=format_date($r["created_date"])?> by <?=$r["created_user"]?></td>
	</tr>
	<? }
	if ($r["updated_date"]) {?>
	<tr>
		<td class="left">Updated</td>
		<td><?=format_date($r["updated_date"])?> by <?=$r["updated_user"]?></td>
	</tr>
	<? }
	if ($r["deleted_date"]) {?>
	<tr>
		<td class="left">Deleted</td>
		<td><?=format_date($r["deleted_date"])?> by <?=$r["deleted_user"]?></td>
	</tr>
	<? } ?>
</table>
<? drawBottom();?>