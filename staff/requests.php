<?php include("include.php");

if (url_action("deletereq")) {
	db_query("DELETE FROM users_requests WHERE id = " . $_GET["id"]);
	url_query_drop("action,id");
} elseif (url_action("invite")) {
	$result = db_query("SELECT id, nickname, email, firstname FROM users WHERE lastlogin IS NULL AND is_active = 1");
	while ($r = db_fetch($result)) {
		$name = (!$r["nickname"]) ? $r["firstname"] : $r["nickname"];
		emailInvite($r["id"], $r["email"], $name);
	}
	url_query_drop("action");
}

echo drawTop();
echo drawJumpToStaff();
echo drawTableStart();
echo drawHeaderRow("", 3);
$result = db_query("SELECT id, lastname, firstname, created_date FROM users_requests WHERE is_active = 1 ORDER BY created_date DESC");
if (db_found($result)) {?>
	<tr>
		<th width="70%">Name</th>
		<th width="30%" class="r">Invited On</th>
		<th></th>
	</tr>
	<? while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="add_edit.php?requestID=<?=$r["id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
		<td class="r"><?=format_date_time($r["created_date"])?></td>
		<td width="16"><?=draw_img("/images/icons/delete.png", url_query_add(array("action"=>"deletereq", "id"=>$r["id"]), false))?></td>
	</tr>
	<?
	}
} else {
	echo drawEmptyResult(getString('staff_requests_empty'));
}
echo drawTableEnd();

//never logged in
echo drawTableStart();
echo drawHeaderRow(getString('staff_never_logged_in'), 3, getString('staff_invite_all'), url_query_add(array("action"=>"invite"), false));
$result = db_query("SELECT id, lastname, firstname, created_date FROM users WHERE lastlogin IS NULL AND is_active = 1 ORDER BY lastname");
if (db_found($result)) {?>
	<tr>
		<th width="70%"><?=getString('name')?></th>
		<th width="30%" class="r"><?=getString('date_created')?></th>
		<th></th>
	</tr>
	<?
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="view.php?id=<?=$r["id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
		<td class="r"><?=format_date($r["created_date"])?></td>
		<?=drawDeleteColumn("Delete user?", $r["id"])?>
	</tr>
	<?
	}
} else {
	echo drawEmptyResult("All users have logged in.");
}
echo drawTableEnd();

echo drawBottom();
?>