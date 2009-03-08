<?php	include("include.php");
drawTop();
echo drawJumpToStaff();
?>
<table class="left" cellspacing="1">
	<?
	if ($is_admin) {
		echo drawHeaderRow("Comings", 2, "new", "add_edit.php");
	} else {
		echo drawHeaderRow("Comings", 2);
	}
	$staff = db_query("SELECT
		u.user_id,
		ISNULL(u.nickname, u.firstname) first, 
		u.lastname last,
		u.title,
		d.departmentName,
		o.name office,
		u.startdate,
		u.imageID,
		u.bio,
		m.width,
		m.height
	FROM users u
	JOIN intranet_offices o ON u.officeID = o.id
	JOIN departments d ON u.departmentID = d.departmentID
	LEFT JOIN intranet_images m ON u.imageID = m.imageID
	WHERE " . db_datediff("u.startdate", "GETDATE()") . " < 60 AND u.is_active = 1
	ORDER BY u.startdate DESC");

	while ($s = db_fetch($staff)) {?>
	<tr>
		<td width="129" height="90" align="center" style="padding:1px;"><?
			if ($s["width"]) {
				$factor    = @(90 / $s["height"]);
				$s["height"] = $s["height"] * $factor;
				$s["width"]  = round($s["width"] * $factor);
				echo "<a href='/staff/view.php?id=" . $s["user_id"] . "'><img src='" . $locale . "staff/" . $s["imageID"] . ".jpg' width='" . $s["width"] . "' height='" . $s["height"] . "' border='0'></a>";
			} else {
				echo "<a href='/staff/view.php?id=" . $s["user_id"] . "'><img src='" . $locale . "images/to-be-taken.png' width='129' height='90' border='0'></a>";
			}
		?></td>
		<td class="text">
			<b><a href="/staff/view.php?id=<?=$s["user_id"]?>"><?=$s["first"]?> <?=$s["last"]?></a></b> &nbsp;<span class="light"><?=format_date($s["startdate"])?></span><br>
			<?=$s["title"]?><br>
			<?=$s["departmentName"]?><br>
			<?=$s["office"]?><br>
			<?=$s["bio"]?>
		</td>
	</tr>
	<? }?>
</table>

<?
$result = db_query("SELECT 
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.title,
			d.departmentName,
			m.width,
			m.height,
			u.user_id, 
			u.imageID,
			u.endDate
			FROM users u
			JOIN departments d ON u.departmentID = d.departmentID
			LEFT JOIN intranet_images m ON u.imageID = m.imageID
			WHERE " . db_datediff("u.endDate", "GETDATE()") . " < 32 ORDER BY endDate DESC");
?>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Goings", 4);?>
	<tr>
		<th width="47"></th>
		<th width="25%" align="left">Name</th>
		<th width="50%" align="left">Title, Department</th>
		<th width="20%" align="right">Last Day</th>
	</tr>
	<? while ($r = db_fetch($result)) {?>
	<tr bgcolor="#FFFFFF" class="helptext" valign="top" height="38">
		<? if ($r["imageID"]) {
			verifyImage($r["imageID"]);
			$factor      = (31 / $r["height"]);
			$r["width"]  = $r["width"]  * $factor;
			$r["height"] = $r["height"] * $factor;
			?>
		<td align="center"><a href="/staff/view.php?id=<?=$r["user_id"]?>"><img src="<?=$locale?>staff/<?=$r["imageID"]?>.jpg" width="<?=$r["width"]?>" height="<?=$r["height"]?>" border="0"></a></td>
		<? } else {?>
		<td></td>
		<? }?>
		<td><a href="/staff/view.php?id=<?=$r["user_id"]?>"><?=$r["first"]?> <?=$r["last"]?></a></td>
		<td><?=$r["title"]?>, <?=$r["departmentName"]?></td>
		<td align="right"><?=format_date($r["endDate"]);?></td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>