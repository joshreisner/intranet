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
	$return = draw_div("panel", 'Jump to ' . drawSelectUser("", $selectedID, $nullable, 0, true, true, "Staff Member:"));
	if ($page['is_admin']) { 
		if ($r = db_grab("SELECT COUNT(*) FROM users_requests")) {
			$return = drawMessage("There are pending <a href='requests.php'>account requests</a> for you to review.") . $return;
		}
		
	}
	return $return;
}

function drawStaffList($where, $searchterms=false) {
	global $page, $_josh;
	$return = drawJumpToStaff() . '<table class="left" cellspacing="1">';
	if ($page['is_admin']) {
		$colspan = 5;
		$return .= drawHeaderRow(false, $colspan, "new", "add_edit.php");
	} else {
		$colspan = 4;
		$return .= drawHeaderRow(false, $colspan);
	}
	if (getOption("channels") && $_SESSION["channel_id"]) {
		$where .= " AND u2c.channel_id = " . $_SESSION["channel_id"];
	}
	$result = db_query("SELECT DISTINCT
							u.id, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone,
							c.title corporationName,
							u.organization_id,
							o.name office, 
							o.isMain,
							u.title, 
							d.departmentName
						FROM users u
						LEFT JOIN users_to_channels u2c ON u.id = u2c.user_id
						LEFT JOIN departments d	ON d.departmentID = u.departmentID 
						LEFT JOIN organizations c ON u.organization_id = c.id
						LEFT JOIN offices o ON o.id = u.officeID
						WHERE " . $where . "
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	$count = db_found($result);
	if ($count) { 
		$return .= '<tr>
			<th style="width:50px;"></th>
			<th style="text-align:left">Name / Office</th>
			<th style="text-align:left">Title / Department</th>
			<th style="text-align:left">Phone</th>';
		if ($page['is_admin']) $return .= '<th></th>';
		$return .= '</tr>';
		if (($count == 1) && $searchterms) {
			$r = db_fetch($result);
			$_josh["slow"] = true;
			url_change("view.php?id=" . $r["id"]);
		} else {
			while ($r = db_fetch($result)) $return .= drawStaffRow($r, $searchterms);
		}
	} else {
		$return .= drawEmptyResult("No staff match those criteria.", $colspan);
	}
	return $return . '</table>';
}

function drawStaffRow($r, $searchterms=false) {
	global $page, $_josh;
	if ($searchterms) {
		global $fields;
		foreach ($fields as $f) {
			if (isset($r[$f])) $r[$f] = format_hilite($r[$f], $searchterms);
		}
	}

	$return  = '<tr height="38">';
	$return .= '<td width="50">' . draw_img($_josh["write_folder"] . '/staff/' . $r["id"] . '-small.jpg', '/staff/view.php?id=' . $r["id"]) . '</td>';
	$return .= '<td><nobr><a href="view.php?id=' . $r["id"] . '">' . $r["lastname"] . ', ' . $r["firstname"] . '</a>';
	if (!$r["isMain"]) $return .= "<br>" . $r["office"];
	$return .= '</nobr></td><td>';
	if ($r["title"]) $return .= $r["title"] . '<br>';
	if ($r["departmentName"]) $return .= '<i>' . $r["departmentName"] . '</i><br>';
	if ($r["corporationName"]) $return .= '<a href="/staff/organizations.php?id=' . $r["organization_id"] . '">' . $r["corporationName"] . '</a>';
	$return .= '</td><td><nobr>' . format_phone($r["phone"]) . '</nobr></td>';
	if ($page['is_admin']) $return .= '<td width="16"><a href="javascript:url_prompt(\'' . url_query_add(array("action"=>"delete", "staffID"=>$r["id"]), false) . '\', \'Delete this staff member?\');"><img src="/images/icons/delete.png" width="16" height="16" border="0"></td>';
	return $return . '</tr>';
}


?>