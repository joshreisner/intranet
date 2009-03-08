<? include("../include.php");
drawTop();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Recent Activity", 4);?>
	<tr>
		<th width="40%" align="left">Contact Record</th>
		<th width="20%" align="left">Action</th>
		<th width="20%">Done By</th>
		<th width="20%" align="right">When</th>
	</tr>
	<?
	$result = db_query("SELECT
			o.id,
			o.is_active,
			i.varchar_02,
			i.varchar_01,
			i.created_date,
			(SELECT COUNT(*) FROM contacts_instances i2 WHERE i2.objectID = o.id) occurrences,
			i.created_user,
			ISNULL(u.nickname, u.firstname) updatename,
			o.is_active
		FROM contacts o
		INNER JOIN contacts_instances	i ON o.instanceCurrentID = i.id
		INNER JOIN users		u ON i.created_user = u.user_id
		ORDER BY i.created_date DESC", 40);
	while ($r = db_fetch($result)) {?>
	<tr class="<?if(!$r["is_active"]){?>-deleted<? }?>">
		<td><a href="contact.php?id=<?=$r["id"]?>"><?=$r["varchar_02"]?>, <?=$r["varchar_01"]?></a></td>
		<td><?if ($r["occurrences"] == 1) {?>New Contact<?} else {?>Update<?}?></td>
		<td align="center"><a href="/staff/view.php?id=<?=$r["created_user"]?>"><?=$r["updatename"]?></a></td>
		<td align="right"><?=format_date($r["created_date"])?></td>
	</tr>
	<? } ?>
</table>
<? drawBottom();?>