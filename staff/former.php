<?	include("../include.php");
drawTop();

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Former Staff", 5);?>
	<?
	$staff = db_query("SELECT 
							u.userID, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone, 
							f.name office, 
							u.title, 
							d.departmentName,
							u.imageID,
							m.height,
							m.width
						FROM users u
						LEFT  JOIN departments d ON d.departmentID = u.departmentID 
						LEFT  JOIN intranet_offices f     ON f.id = u.officeID
						LEFT  JOIN intranet_images m      ON u.imageID = m.imageID
						WHERE u.isactive = 0
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	while ($s = db_fetch($staff)) {?>
	<tr height="38">
		<? if ($s["imageID"]) {
			verifyImage($s["imageID"]);
			$factor      = (31 / $s["height"]);
			$s["width"]  = $s["width"]  * $factor;
			$s["height"] = $s["height"] * $factor;
			?>
		<td width="47" align="center"><a href="/staff/view.php?id=<?=$s["userID"]?>"><img src="/data/staff/<?=$s["imageID"]?>.jpg" width="<?=$s["width"]?>" height="<?=$s["height"]?>" border="0"></a></td>
		<?} else {?>
		<td>&nbsp;</td>
		<?}?>
		<td><nobr><a href="view.php?id=<?=$s["userID"]?>"><?=$s["lastname"]?>, <?=$s["firstname"]?></a></nobr></td>
		<td><?=$s["title"]?></td>
		<td><?=$s["office"]?></td>
		<td align="right"><nobr><?=format_phone($s["phone"])?></nobr></td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>