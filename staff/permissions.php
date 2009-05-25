<?
include("../include.php");

if ($posting) { //update permissions
	db_query("DELETE FROM users_to_modules WHERE user_id = " . $_GET["id"]);
	foreach ($_POST as $key=>$value) {
		@list($control, $module_id) = explode("_", $key);
		if ($control == "chk") db_query("INSERT INTO users_to_modules ( user_id, module_id ) VALUES ( {$_GET["id"]}, {$module_id} )");
	}
	url_change("view.php?id=" . $_GET["id"]);
}

drawTop();
drawNavigation();
$u = db_grab("SELECT ISNULL(u.nickname, u.firstname) first, u.lastname last FROM users u WHERE u.id = " . $_GET["id"]);
?>
<table class="left" cellspacing="1">
	<form method="post" name="permissions" action="<?=$_josh["request"]["path_query"]?>">
	<?=drawHeaderRow("Edit Permissions",2);?>
	<tr>
		<td class="left">User</td>
		<td><b><?=$u["first"]?> <?=$r["last"]?></b></td>
	</tr>
	<tr>
		<td class="left">Permissions</td>
		<td><table class="nospacing">
		<?
		$result = db_query("SELECT 
								m.id,
								m.name,
								(SELECT COUNT(*) FROM users_to_modules a WHERE a.module_id = m.id AND a.user_id = {$_GET["id"]}) is_admin
							FROM modules m
							WHERE m.is_active = 1
							ORDER BY m.name");
		while ($r = db_fetch($result)) {?>
				<tr>
					<td><?=draw_form_checkbox("chk_" . $r["id"], $r["is_admin"])?></td>
					<td><?=$r["name"]?></td>
				</tr>
		<? }?>
			</table>
		</td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?=draw_form_submit("save changes");?></td>
	</tr>
	</form>
</table>
<? drawBottom();?>