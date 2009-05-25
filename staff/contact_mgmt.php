<? include("../include.php");

drawTop();
drawNavigation();

$result = db_query("SELECT
						u.id,
						u.lastname last,
						ISNULL(u.nickname, u.firstname) first,
						u.title,
						u.homephone, 
						u.homecell 
					FROM users u
					WHERE u.rankid < 8 AND u.is_active = 1
					ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
?>
<table class="left">
	<?=drawHeaderRow("Management Contact Numbers", 4);?>
	<tr bgcolor="#F6F6F6" class="small">
		<th align="left">Name</th>
		<th align="left">Title</th>
		<th align="left">Home #</th>
		<th align="left">Cell #</th>
	</tr>
	<? while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="view.php?id=<?=$r["user_id"]?>"><?=$r["first"]?> <?=$r["last"]?></a></td>
		<td><?=$r["title"]?></td>
		<td width="95"><?=format_phone($r["homephone"])?></td>
		<td width="95"><?=format_phone($r["homecell"])?></td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>