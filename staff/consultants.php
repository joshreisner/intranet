<?
include("../include.php");

if (isset($_GET["deleteID"])) {
	$r = db_grab("SELECT endDate FROM users WHERE user_id = " . $_GET["deleteID"]);
	if ($r["endDate"]) {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE() WHERE user_id = " . $_GET["deleteID"]);
	} else {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE(), endDate = GETDATE() WHERE user_id = " . $_GET["deleteID"]);
	}
	url_query_drop("deleteID");
}

drawTop();
drawNavigation();

?>
<table class="left" cellspacing="1">
	<? if ($module_admin) {
		echo drawHeaderRow("Consultants", 6, "add new staff member", "add_edit.php");
	} else {
		echo drawHeaderRow("Consultants", 5);
	} ?>
	<tr>
		<th></th>
		<th align="left">Name</th>
		<th align="left">Title</th>
		<th align="left">Location</th>
		<th align="left">Phone</th>
	<? if ($module_admin) {?>
		<th></th>
	<? } ?>
	</tr>
	<?
		
	$result = db_query("SELECT 
							u.user_id, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone, 
							f.name office, 
							u.title, 
							d.departmentName
							r.isPayroll
						FROM users u
						JOIN intranet_ranks r ON u.rankID = r.id
						LEFT  JOIN departments d ON d.departmentID = u.departmentID 
						LEFT  JOIN intranet_offices f     ON f.id = u.officeID
						WHERE u.is_active = 1 AND r.isPayroll = 0
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	while ($r = db_fetch($result)) {?>
	<tr height="38">
		<? verifyImage($r["user_id"]);?>
		<td width="47" align="center"><?=draw_img($locale . "staff/" . $r["user_id"] . "-small.jpg", "/staff/view.php?id=" . $r["user_id"])?></td>
		<td><nobr><a href="view.php?id=<?=$r["user_id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></nobr></td>
		<td><?=$r["title"]?></td>
		<td><?=$r["office"]?></td>
		<td align="right"><nobr><?=format_phone($r["phone"])?></nobr></td>
		<?=deleteColumn("Delete this staff member?", $r["user_id"])?>
	</tr>
	<? }?>
</table>
<? drawBottom();?>