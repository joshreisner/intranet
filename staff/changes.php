<?php	include("include.php");
drawTop();
echo drawJumpToStaff();
?>
<table class="left" cellspacing="1">
	<?
	if ($module_admin) {
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
		u.bio
	FROM users u
	LEFT JOIN intranet_offices o ON u.officeID = o.id
	LEFT JOIN departments d ON u.departmentID = d.departmentID
	WHERE " . db_datediff("u.startdate", "GETDATE()") . " < 60 AND u.is_active = 1
	ORDER BY u.startdate DESC");
	if (db_found($staff)) {
		while ($s = db_fetch($staff)) {?>
		<tr>
			<td width="135" height="60" align="center" style="padding:0px;"><?
				verifyImage($s["user_id"]);
				echo draw_img($_josh["write_folder"] . "/staff/" . $s["user_id"] . "-medium.jpg", "/staff/view.php?id=" . $s["user_id"]);
				?></td>
			<td class="text">
				<b><a href="/staff/view.php?id=<?=$s["user_id"]?>"><?=$s["first"]?> <?=$s["last"]?></a></b> &nbsp;<span class="light"><?=format_date($s["startdate"])?></span><br>
				<?=$s["title"]?><br>
				<? if (getOption("staff_showdept")) echo $s["departmentName"] . "<br>";?>
				<? if (getOption("staff_office")) echo $s["office"] . "<br>";?>
				<?=$s["bio"]?>
			</td>
		</tr>
		<? }
	} else {
		echo drawEmptyResult("No staff are listed as having started in the last two months.", 2);
	}
echo drawTableEnd();

echo drawTableStart();
$result = db_query("SELECT 
			u.user_id,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.title,
			d.departmentName,
			u.user_id, 
			u.endDate
			FROM users u
			LEFT JOIN departments d ON u.departmentID = d.departmentID
			WHERE " . db_datediff("u.endDate", "GETDATE()") . " < 32 ORDER BY endDate DESC");
	echo drawHeaderRow("Goings", 4);
	if (db_found($result)) {?>
		<tr>
			<th width="47"></th>
			<th width="25%" align="left">Name</th>
			<th width="50%" align="left">Title, Department</th>
			<th width="20%" align="right">Last Day</th>
		</tr>
		<? while ($r = db_fetch($result)) {
			verifyImage($r["user_id"]);
		?>
		<tr bgcolor="#FFFFFF" class="helptext" valign="top" height="38">
			<td align="center"><a href="/staff/view.php?id=<?=$r["user_id"]?>"><img src="<?=$_josh["write_folder"]?>/staff/<?=$r["user_id"]?>.jpg" width="<?=$r["width"]?>" height="<?=$r["height"]?>" border="0"></a></td>
			<td><a href="/staff/view.php?id=<?=$r["user_id"]?>"><?=$r["first"]?> <?=$r["last"]?></a></td>
			<td><?=$r["title"]?>, <?=$r["departmentName"]?></td>
			<td align="right"><?=format_date($r["endDate"]);?></td>
		</tr>
		<? }
	} else {
		echo drawEmptyResult("No staff left in the last month.", 4);
	}
echo drawTableEnd();
drawBottom();?>