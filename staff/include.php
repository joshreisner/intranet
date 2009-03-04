<?php
include("../include.php");

if (url_action("delete")) {
	if (!isset($_GET["staffID"]) && isset($_GET["id"])) $_GET["staffID"] = $_GET["id"];
	$r = db_grab("SELECT firstname, lastname, endDate FROM intranet_users WHERE userID = " . $_GET["staffID"]);
	if ($r["endDate"]) {
		db_query("UPDATE intranet_users SET isActive = 0, deletedBy = {$_SESSION["user_id"]}, deletedOn = GETDATE() WHERE userID = " . $_GET["staffID"]);
	} else {
		db_query("UPDATE intranet_users SET isActive = 0, deletedBy = {$_SESSION["user_id"]}, deletedOn = GETDATE(), endDate = GETDATE() WHERE userID = " . $_GET["staffID"]);
	}
	if ($locale == "/_seedco/") {	
		email("jreisner@seedco.org,pchoi@seedco.org", 
		"<a href='http://intranet.seedco.org/staff/view.php?id=" . $_GET["staffID"] . "'>" . $r["firstname"] . " " . $r["lastname"] . "</a> was just deactivated on the Intranet.", 
		"Intranet: Staff Deleted");
	}
	url_query_drop("action,staffID");
}

function drawJumpToStaff($selectedID=false) {
	global $isAdmin;
	$nullable = ($selectedID === false);
	$return = '
		<table class="message">
			<tr>
				<td class="gray">Jump to ' . drawSelectUser("", $selectedID, $nullable, 0, true, true, "Staff Member:") . '</td>
			</tr>
		</table>';
	if ($isAdmin) { 
		if ($r = db_grab("SELECT COUNT(*) FROM users_requests")) {
			$return = drawServerMessage("There are pending <a href='requests.php'>account requests</a> for you to review.") . $return;
		}
		
	}
	return $return;
}

function drawStaffList($where, $searchterms=false) {
	global $isAdmin, $_josh;
	$return = drawJumpToStaff() . '<table class="left" cellspacing="1">';
	if ($isAdmin) {
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
	if ($isAdmin) $return .= '<th></th>';
	$return .= '</tr>';
	
	$result = db_query("SELECT 
							u.userID, 
							u.lastname,
							ISNULL(u.nickname, u.firstname) firstname, 
							u.bio, 
							u.phone,
							c.description corporationName,
							u.corporationID,
							o.name office, 
							o.isMain,
							u.title, 
							d.departmentName,
							u.imageID,
							m.height,
							m.width
						FROM intranet_users u
						LEFT JOIN intranet_departments d	ON d.departmentID = u.departmentID 
						LEFT JOIN organizations c			ON u.corporationID = c.id
						LEFT JOIN intranet_offices o		ON o.id = u.officeID
						LEFT JOIN intranet_images m			ON u.imageID = m.imageID
						WHERE " . $where . "
						ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)");
	$count = db_found($result);
	if ($count) { 
		if (($count == 1) && $searchterms) {
			$r = db_fetch($result);
			$_josh["slow"] = true;
			url_change("view.php?id=" . $r["userID"]);
		} else {
			while ($r = db_fetch($result)) $return .= drawStaffRow($r, $searchterms);
		}
	} else {
		$return .= drawEmptyResult("No staff match those criteria.", $colspan);
	}
	return $return . '</table>';
}

function drawStaffRow($r, $searchterms=false) {
	global $isAdmin, $locale;
	if ($searchterms) {
		global $fields;
		foreach ($fields as $f) {
			if (isset($r[$f])) $r[$f] = format_hilite($r[$f], $searchterms);
		}
	}

	$return  = '<tr height="38">';
	if ($r["imageID"]) {
		verifyImage($r["imageID"]);
		$factor      = @(31 / $r["height"]);
		$r["width"]  = $r["width"]  * $factor;
		$r["height"] = $r["height"] * $factor;
		$return .= '<td width="47" align="center"><a href="/staff/view.php?id=' . $r["userID"] . '"><img src="' . $locale . 'staff/' . $r["imageID"] . '.jpg" width="' . $r["width"] . '" height="' . $r["height"] . '" border="0"></a></td>';
	} else {
		$return .= '<td>&nbsp;</td>';
	}
	$return .= '<td><nobr><a href="view.php?id=' . $r["userID"] . '">' . $r["lastname"] . ', ' . $r["firstname"] . '</a>';
	if (!$r["isMain"]) $return .= "<br>" . $r["office"];
	$return .= '</nobr></td><td>';
	if ($r["title"]) $return .= $r["title"] . '<br>';
	if ($r["departmentName"]) $return .= '<i>' . $r["departmentName"] . '</i><br>';
	if ($r["corporationName"]) $return .= '<a href="/staff/organizations.php?id=' . $r["corporationID"] . '">' . $r["corporationName"] . '</a>';
	$return .= '</td>
		<td align="right"><nobr>' . format_phone($r["phone"]) . '</nobr></td>
		';
		if ($isAdmin) $return .= '<td><a href="javascript:url_prompt(\'' . url_query_add(array("action"=>"delete", "staffID"=>$r["userID"]), false) . '\', \'Delete this staff member?\');"><img src="' . $locale . 'images/icons/delete.gif" width="16" height="16" border="0"></td>';
	return $return . '</tr>';
}


?>