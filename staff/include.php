<?php
include("../include.php");

if (url_action("delete")) {
	if (!isset($_GET["staffID"]) && isset($_GET["id"])) $_GET["staffID"] = $_GET["id"];
	$r = db_grab("SELECT firstname, lastname, endDate FROM users WHERE user_id = " . $_GET["staffID"]);
	if ($r["endDate"]) {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE() WHERE user_id = " . $_GET["staffID"]);
	} else {
		db_query("UPDATE users SET is_active = 0, deleted_user = {$_SESSION["user_id"]}, deleted_date = GETDATE(), endDate = GETDATE() WHERE user_id = " . $_GET["staffID"]);
	}
	if ($locale == "/_seedco/") {	
		email("jreisner@seedco.org,pchoi@seedco.org", 
		"<a href='http://intranet.seedco.org/staff/view.php?id=" . $_GET["staffID"] . "'>" . $r["firstname"] . " " . $r["lastname"] . "</a> was just deactivated on the Intranet.", 
		"Intranet: Staff Deleted");
	}
	url_query_drop("action,staffID");
}

function drawJumpToStaff($selectedID=false) {
	global $is_admin;
	$nullable = ($selectedID === false);
	$return = '
		<table class="message">
			<tr>
				<td class="gray">Jump to ' . drawSelectUser("", $selectedID, $nullable, 0, true, true, "Staff Member:") . '</td>
			</tr>
		</table>';
	if ($is_admin) { 
		if ($r = db_grab("SELECT COUNT(*) FROM users_requests")) {
			$return = drawServerMessage("There are pending <a href='requests.php'>account requests</a> for you to review.") . $return;
		}
		
	}
	return $return;
}

function drawStaffList($where, $searchterms=false) {
	global $is_admin, $_josh;
	$return = drawJumpToStaff() . '<table class="left" cellspacing="1">';
	if ($is_admin) {
		$colspan = 5;
		$return .= drawHeaderRow(false, $colspan, "new", "add_edit.php");
	} else {
		$colspan = 4;
		$return .= drawHeaderRow(false, $colspan);
	}
	$return .= '<tr>
		<th style="width:50px;"></th>
		<th style="text-align:left">Name / Office</th>
		<th style="text-align:left">Title / Department</th>
		<th style="text-align:left">Phone</th>';
	if ($is_admin) $return .= '<th></th>';
	$return .= '</tr>';
	
	$result = db_query("SELECT 
							u.user_id, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone,
							c.description corporationName,
							u.corporationID,
							o.name office, 
							o.isMain,
							u.title, 
							d.departmentName
						FROM users u
						LEFT JOIN departments d	ON d.departmentID = u.departmentID 
						LEFT JOIN organizations c			ON u.corporationID = c.id
						LEFT JOIN intranet_offices o		ON o.id = u.officeID
						WHERE " . $where . "
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	$count = db_found($result);
	if ($count) { 
		if (($count == 1) && $searchterms) {
			$r = db_fetch($result);
			$_josh["slow"] = true;
			url_change("view.php?id=" . $r["user_id"]);
		} else {
			while ($r = db_fetch($result)) $return .= drawStaffRow($r, $searchterms);
		}
	} else {
		$return .= drawEmptyResult("No staff match those criteria.", $colspan);
	}
	return $return . '</table>';
}

function drawStaffRow($r, $searchterms=false) {
	global $is_admin, $locale;
	if ($searchterms) {
		global $fields;
		foreach ($fields as $f) {
			if (isset($r[$f])) $r[$f] = format_hilite($r[$f], $searchterms);
		}
	}

	$return  = '<tr height="38">';
	verifyImage($r["user_id"]);
	$return .= '<td width="50">' . draw_img($locale . 'staff/' . $r["user_id"] . '-thumbnail.jpg', '/staff/view.php?id=' . $r["user_id"]) . '</td>';
	$return .= '<td><nobr><a href="view.php?id=' . $r["user_id"] . '">' . $r["lastname"] . ', ' . $r["firstname"] . '</a>';
	if (!$r["isMain"]) $return .= "<br>" . $r["office"];
	$return .= '</nobr></td><td>';
	if ($r["title"]) $return .= $r["title"] . '<br>';
	if ($r["departmentName"]) $return .= '<i>' . $r["departmentName"] . '</i><br>';
	if ($r["corporationName"]) $return .= '<a href="/staff/organizations.php?id=' . $r["corporationID"] . '">' . $r["corporationName"] . '</a>';
	$return .= '</td><td><nobr>' . format_phone($r["phone"]) . '</nobr></td>';
	if ($is_admin) $return .= '<td width="16"><a href="javascript:url_prompt(\'' . url_query_add(array("action"=>"delete", "staffID"=>$r["user_id"]), false) . '\', \'Delete this staff member?\');"><img src="' . $locale . 'images/icons/delete.gif" width="16" height="16" border="0"></td>';
	return $return . '</tr>';
}


?>