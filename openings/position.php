<?
include("../include.php");
drawTop();

$r = db_grab("SELECT 
		j.id,
		j.title,
		j.description,
		c.description corporationName,
		o.name office,
		j.createdOn, 
		j.updatedOn,
		j.deletedOn,
		u1.firstname createdByFirst,
		u1.lastname createdByLast,
		u2.firstname updatedByFirst,
		u2.lastname updatedByLast,
		u3.firstname deletedByFirst,
		u3.lastname updatedByLast
	FROM intranet_jobs j
	LEFT JOIN organizations c ON j.corporationID = c.id
	LEFT JOIN intranet_offices o ON j.officeID = o.id
	LEFT JOIN users u1 ON j.createdBy = u1.userID
	LEFT JOIN users u2 ON j.updatedBy = u2.userID
	LEFT JOIN users u3 ON j.deletedBy = u3.userID
	
	WHERE j.id = " . $_GET["id"]);
	$r["createdBy"] = ($r["createdByFirst"]) ? $r["createdByFirst"] . " " . $r["createdByLast"] : false;
	$r["updatedBy"] = ($r["updatedByFirst"]) ? $r["updatedByFirst"] . " " . $r["updatedByLast"] : false;
	$r["deletedBy"] = ($r["deletedByFirst"]) ? $r["deletedByFirst"] . " " . $r["deletedByLast"] : false;
?>
<table class="left" cellspacing="1">
	<? if ($isAdmin) {
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
	<? if ($r["createdOn"]) {?>
	<tr>
		<td class="left">Posted</td>
		<td><?=format_date($r["createdOn"])?> by <?=$r["createdBy"]?></td>
	</tr>
	<? }
	if ($r["updatedOn"]) {?>
	<tr>
		<td class="left">Updated</td>
		<td><?=format_date($r["updatedOn"])?> by <?=$r["updatedBy"]?></td>
	</tr>
	<? }
	if ($r["deletedOn"]) {?>
	<tr>
		<td class="left">Deleted</td>
		<td><?=format_date($r["deletedOn"])?> by <?=$r["deletedBy"]?></td>
	</tr>
	<? } ?>
</table>
<? drawBottom();?>