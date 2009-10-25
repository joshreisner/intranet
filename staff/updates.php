<?
include("../include.php");
echo drawTop();

$users = db_query("select 
		ISNULL(u.nickname, u.firstname) first,
		u.lastname last,
		u.id,
		u.title,
		d.departmentName,
		u.officeid,
		r.isPayroll,
		u.lastlogin, 
		u.updated_date, 
		" . db_datediff("u.updated_date", "GETDATE()") . " recent 
	FROM users u
	JOIN departments d on u.departmentID = d.departmentID
	JOIN intranet_ranks r on u.rankID = r.id
	WHERE u.is_active = 1 and (u.homeaddress1 = '' OR u.homeaddress1 IS NULL)
	ORDER BY lastname");
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Staff Profiles Needing Update (" . db_found($users) . ")", 4);?>
	<tr>
		<th width="25%" align="left">email</th>
		<th width="30%" align="left">department</th>
		<th width="30%" align="left">title</th>
		<th width="15%" align="right">last login</th>
	</tr>
	<? while ($u = db_fetch($users)) {?>
	<tr>
		<td><a href="/staff/view.php?id=<?=$u["user_id"]?>"><?=$u["first"]?> <?=$u["last"]?></a></td>
		<td><?=$u["departmentName"]?>
		<td><?=$u["title"]?>
		<td align="right"><?=format_date($u["lastlogin"])?></td>
	</tr>
	<? }?>
</table>
<?=drawBottom();?>