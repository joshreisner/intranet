<?php	include("include.php");
echo drawTop();
echo drawJumpToStaff();
?>
<table class="left" cellspacing="1">
	<?
	if ($page['is_admin']) {
		echo drawHeaderRow("Comings", 2, "new", "add_edit.php");
	} else {
		echo drawHeaderRow("Comings", 2);
	}
	$staff = db_query("SELECT
		u.id,
		ISNULL(u.nickname, u.firstname) first, 
		u.lastname last,
		u.title,
		d.departmentName,
		o.name office,
		u.startdate,
		u.bio
	FROM users u
	LEFT JOIN offices o ON u.officeID = o.id
	LEFT JOIN departments d ON u.departmentID = d.departmentID
	WHERE " . db_datediff("u.startdate", "GETDATE()") . " < 60 AND u.is_active = 1
	ORDER BY u.startdate DESC");
	if (db_found($staff)) {
		while ($s = db_fetch($staff)) {?>
		<tr>
			<td width="135" height="60" align="center" style="padding:0px;"><?
				echo draw_img($_josh["write_folder"] . "/staff/" . $s["id"] . "-medium.jpg", "/staff/view.php?id=" . $s["id"]);
				?></td>
			<td class="text">
				<b><a href="/staff/view.php?id=<?=$s["id"]?>"><?=$s["first"]?> <?=$s["last"]?></a></b> &nbsp;<span class="light"><?=format_date($s["startdate"])?></span><br>
				<?=$s["title"]?><br>
				<? if (getOption("staff_showdept")) echo $s["departmentName"] . "<br>";?>
				<? if (getOption("staff_showoffice")) echo $s["office"] . "<br>";?>
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
			u.id,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.title,
			d.departmentName,
			u.id, 
			u.endDate
			FROM users u
			LEFT JOIN departments d ON u.departmentID = d.departmentID
			WHERE u.is_active = 0 AND " . db_datediff("u.endDate", "GETDATE()") . " < 32 ORDER BY endDate DESC");
	echo drawHeaderRow("Goings", 4);
	if (db_found($result)) {?>
		<tr>
			<th width="47"></th>
			<th width="25%">Name</th>
			<th width="50%">Title, Department</th>
			<th width="20%" class="r">Day Removed</th>
		</tr>
		<? while ($r = db_fetch($result)) {?>
		<tr bgcolor="#FFFFFF" class="helptext" valign="top" height="38">
			<td align="center"><?=draw_img($_josh["write_folder"] . '/staff/' . $r["id"] . '-small.jpg', "/staff/view.php?id=" . $r["id"])?></a></td>
			<td><a href="/staff/view.php?id=<?=$r["id"]?>"><?=$r["first"]?> <?=$r["last"]?></a></td>
			<td><?=$r["title"]?>, <?=$r["departmentName"]?></td>
			<td align="right"><?=format_date($r["endDate"]);?></td>
		</tr>
		<? }
	} else {
		echo drawEmptyResult("No staff left in the last month.", 4);
	}
echo drawTableEnd();
echo drawBottom();?>