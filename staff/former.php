<?	include("../include.php");
echo drawTop();

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Former Staff", 5);?>
	<?
	$staff = db_query("SELECT 
							u.id, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone, 
							f.name office, 
							u.title, 
							d.departmentName
						FROM users u
						LEFT  JOIN departments d ON d.departmentID = u.departmentID 
						LEFT  JOIN offices f     ON f.id = u.officeID
						WHERE u.is_active = 0
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	while ($s = db_fetch($staff)) {?>
	<tr height="38">
		<td width="47" align="center"><?=draw_img(DIRECTORY_WRITE . "/staff/" . $r["user_id"] . "-small.jpg", "/staff/view.php?id=" . $r["user_id"])?></td>
		<td><nobr><a href="view.php?id=<?=$s["user_id"]?>"><?=$s["lastname"]?>, <?=$s["firstname"]?></a></nobr></td>
		<td><?=$s["title"]?></td>
		<td><?=$s["office"]?></td>
		<td align="right"><nobr><?=format_phone($s["phone"])?></nobr></td>
	</tr>
	<? }?>
</table>
<?=drawBottom();?>