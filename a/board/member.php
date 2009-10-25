<?php
include("../../include.php");
echo drawTop();
	
$r = db_grab("SELECT
		m.firstname,
		m.lastname,
		m.bio,
		m.board_position,
		m.employment,
		o.description organization
	FROM board_members m
	JOIN organizations o ON m.organization_id = o.id
	WHERE m.id = " . $_GET["id"]);
?>
<table class="left" cellspacing="1">
	<? if ($page['is_admin']) {
		echo drawHeaderRow("Board Member", 2, "edit", "member_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("Board Member", 2);
	}?>
	<tr>
		<td class="left">Name</td>
		<td><h1><?=$r["firstname"]?> <?=$r["lastname"]?></h1></td>
	</tr>
	<tr>
		<td class="left">Organization</td>
		<td><?=$r["organization"]?></td>
	</tr>
	<tr>
		<td class="left">Position on Board</td>
		<td><?=$r["board_position"]?></td>
	</tr>
	<tr>
		<td class="left">Employment</td>
		<td><?=$r["employment"]?></td>
	</tr>
	<tr>
		<td class="left">Bio</td>
		<td class="text"><?=$r["bio"]?></td>
	</tr>
</table>
<?=drawBottom(); ?>