<?php	include("include.php");
echo drawTop();
//echo drawJumpToStaff();

echo drawStaffList('u.is_active = 1 AND ' . db_datediff('u.startdate') . ' < 60', getString('staff_new_empty'), array('add_edit.php'=>getString('add_new')), getString('staff_new'));

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