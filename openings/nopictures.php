<?
include("../include.php");

drawTop();


$result = db_query("SELECT
					u.user_id,
					u.firstname,
					u.lastname,
					u.title,
					o.name office
				FROM users u
				JOIN intranet_offices o on u.officeID = o.officeID
				JOIN intranet_ranks r ON u.rankID = r.id
				WHERE u.imageID is null and u.is_active = 1 and r.ispayroll = 1
				ORDER BY o.name, u.lastname, u.firstname");
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Staff Without Pictures", 3)?>
	<tr>
		<th align="left" width="33%">Name</th>
		<th align="left" width="33%">Title</th>
		<th align="left" width="33%">Office</th>
	</tr>
	<? while ($r = db_fetch($result)) {?>
	<tr>
		<td width="33%"><a href="/staff/view.php?id=<?=$r["user_id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
		<td width="33%"><?=$r["title"]?></td>
		<td width="33%"><?=$r["office"]?></td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>