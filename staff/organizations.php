<?
include("include.php");

if (isset($_GET["deleteID"])) {
	if (db_grab("SELECT endDate FROM users WHERE user_id = " . $_GET["deleteID"])) {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE() WHERE user_id = " . $_GET["deleteID"]);
	} else {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE(), endDate = GETDATE() WHERE user_id = " . $_GET["deleteID"]);
	}
	url_query_drop("deleteID");
}

$orgs = array();
if (getOption("staff_allowshared")) {
	if (!isset($_GET["id"])) $_GET["id"] = 0;
	$orgs[0] = "Shared";
} else {
	if (!isset($_GET["id"])) $_GET["id"] = 1;
}
$orgs = db_array("SELECT id, description FROM organizations ORDER BY description", $orgs);
drawTop();
?>
<table class="navigation staff" cellspacing="1">
	<tr class="staff-hilite">
		<? foreach ($orgs as $key=>$value) {?>
		<td width="14.28%"<? if ($_GET["id"] == $key) {?> class="selected"<? }?>><? if ($_GET["id"] != $key) {?><a href="organizations.php?id=<?=$key?>"><?} else {?><b><?}?><?=$value?></b></a></td>
		<? }?>
	</tr>
</table>

<?
$where = ($_GET["id"] == 0) ? " IS NULL " : " = " . $_GET["id"];
echo drawStaffList("u.is_active = 1 AND u.corporationID " . $where);

drawBottom();?>