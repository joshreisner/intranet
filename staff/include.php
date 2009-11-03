<?php
include("../include.php");

if (url_action("delete")) {
	if (!isset($_GET["staffID"]) && isset($_GET["id"])) $_GET["staffID"] = $_GET["id"];
	$r = db_grab("SELECT firstname, lastname, endDate FROM users WHERE id = " . $_GET["staffID"]);
	if ($r["endDate"]) {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE() WHERE id = " . $_GET["staffID"]);
	} else {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE(), endDate = GETDATE() WHERE id = " . $_GET["staffID"]);
	}
	if (getOption("staff_alertdelete")) emailAdmins("<a href='" . url_base() . "/staff/view.php?id=" . $_GET["staffID"] . "'>" . $r["firstname"] . " " . $r["lastname"] . "</a> was just deactivated on the Intranet.", "Intranet: Staff Deleted");
	url_query_drop("action,staffID");
}

function drawJumpToStaff($selectedID=false) {
	global $page;
	$nullable = ($selectedID === false);
	$return = draw_div("panel", getString('jump_to') . ' ' . drawSelectUser("", $selectedID, $nullable, 0, true, true, "Staff Member:"));
	if ($page['is_admin']) { 
		if ($r = db_grab("SELECT COUNT(*) FROM users_requests")) {
			$return = drawMessage("There are pending <a href='requests.php'>account requests</a> for you to review.") . $return;
		}
		
	}
	return $return;
}
?>