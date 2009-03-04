<?php include("include.php");

if (url_action("deletereq")) {
	db_query("DELETE FROM users_requests WHERE id = " . $_GET["id"]);
	url_query_drop("action,id");
} elseif (url_action("invite")) {
	$result = db_query("SELECT userID, nickname, email, firstname FROM intranet_users WHERE lastlogin IS NULL AND isactive = 1");
	while ($r = db_fetch($result)) {
		$name = (!$r["nickname"]) ? $r["firstname"] : $r["nickname"];
		email_invite($r["userID"], $r["email"], $name);
	}
	url_query_drop("action");
}

drawTop();
echo drawJumpToStaff();
echo drawTableStart();
echo drawHeaderRow("", 3);
$result = db_query("SELECT id, lastname, firstname, createdOn FROM users_requests ORDER BY createdOn DESC");
if (db_found($result)) {?>
	<tr>
		<th width="70%">Name</th>
		<th width="30%" class="r">Invited On</th>
		<th></th>
	</tr>
	<? while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="add_edit.php?requestID=<?=$r["id"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
		<td class="r"><?=format_date_time($r["createdOn"])?></td>
		<td width="16"><?=draw_img($locale . "images/icons/delete.gif", url_query_add(array("action"=>"deletereq", "id"=>$r["id"]), false))?></td>
	</tr>
	<?
	}
} else {
	echo drawEmptyResult("No pending requests!");
}
echo drawTableEnd();

echo drawTableStart();
echo drawHeaderRow("Never Logged In", 3, "invite them all", url_query_add(array("action"=>"invite"), false));
$result = db_query("SELECT userid, lastname, firstname, createdOn FROM intranet_users WHERE lastlogin IS NULL AND isactive = 1 ORDER BY lastname");
if (db_found($result)) {?>
	<tr>
		<th width="70%">Name</th>
		<th width="30%" class="r">Created Date</th>
		<th></th>
	</tr>
	<?
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="view.php?id=<?=$r["userid"]?>"><?=$r["lastname"]?>, <?=$r["firstname"]?></a></td>
		<td class="r"><?=format_date_time($r["createdOn"])?></td>
		<?=deleteColumn("Delete user?", $r["userid"])?>
	</tr>
	<?
	}
} else {
	echo drawEmptyResult("No pending requests!");
}
echo drawTableEnd();

drawBottom();
?>