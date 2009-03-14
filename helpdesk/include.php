<?php include("../include.php");

//kick out user if not administrator ~ should be done with page info
if (!$module_admin && $page["is_admin"]) url_change("/helpdesk/");

//department may become settable
if (url_id("dept")) {
	$departmentID = $_GET["dept"];
} else {
	$departmentID = ($_SESSION["isHelpdesk"]) ? $_SESSION["departmentID"] : 8;
}

//handle ticket delete
if (url_action("delete")) {
	db_query("DELETE FROM helpdesk_tickets_attachments WHERE ticketID = " . $_GET["ticketID"]);
	db_query("DELETE FROM helpdesk_tickets_followups WHERE ticketID = " . $_GET["ticketID"]);
	db_query("DELETE FROM helpdesk_tickets WHERE id = " . $_GET["ticketID"]);
	url_query_drop("action, ticketID");
}

//filter data
$filtered = false;
if (isset($_GET["month"]) && isset($_GET["year"])) {
	$filtered = true;
	$default = $_GET["month"] . "/" . $_GET["year"];
	$where = " AND MONTH(t.created_date) = " . $_GET["month"] . " AND YEAR(t.created_date) = " . $_GET["year"] . " AND t.departmentID = " . $departmentID;
	$total = db_grab("SELECT 
		(SELECT SUM(t.timeSpent) minutes FROM helpdesk_tickets t WHERE MONTH(t.created_date) = " . $_GET["month"] . " AND YEAR(t.created_date) = " . $_GET["year"] . " AND departmentID = " . $departmentID . ") minutes, 
		MONTH(MIN(created_date)) month,
		YEAR(MIN(created_date)) year
		FROM helpdesk_tickets WHERE departmentID = " . $departmentID);
} else {
	$where = " AND t.departmentID = " . $departmentID;
	$default = "";
	$total = db_grab("SELECT 
		SUM(timeSpent) minutes, 
		MONTH(MIN(created_date)) month,
		YEAR(MIN(created_date)) year
		FROM helpdesk_tickets WHERE departmentID = " . $departmentID);
}

//run dropdown updates owner, status, priority, department
if (isset($_GET["ticketID"])) {
	if (isset($_GET["newOwner"])) {
		if (empty($_GET["newOwner"])) $_GET["newOwner"] = "NULL";
		$r = db_grab("SELECT statusID FROM helpdesk_tickets WHERE id = " . $_GET["ticketID"]);
		if (!$r) {
			$r = "NULL";
		} elseif ($r == 1) {
			$r = 2;
		}
        db_query("UPDATE helpdesk_tickets SET ownerID = {$_GET["newOwner"]}, statusID = {$r}, updated_date  = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
        if ($_GET["newOwner"] != "NULL") emailITticket($_GET["ticketID"], "assign");
	} elseif  (isset($_GET["newStatus"])) {
		if (empty($_GET["newStatus"])) $_GET["newStatus"] = "NULL";
		if ($_GET["newStatus"] == 9) {
			db_query("UPDATE helpdesk_tickets SET closedDate = GETDATE() WHERE id = " . $_GET["ticketID"]);
	        emailITticket($_GET["ticketID"], "closed");
        }
        db_query("UPDATE helpdesk_tickets SET statusID = {$_GET["newStatus"]}, updated_date  = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif  (isset($_GET["newPriority"])) {
        db_query("UPDATE helpdesk_tickets SET priorityID = {$_GET["newPriority"]}, updated_date  = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
		//email mohammed for critical
	} elseif (isset($_GET["newDepartment"])) {
		db_query("UPDATE helpdesk_tickets SET departmentID = {$_GET["newDepartment"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newType"])) {
		if (empty($_GET["newType"])) $_GET["newType"] = "NULL";
		db_query("UPDATE helpdesk_tickets SET typeID = {$_GET["newType"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newTime"])) {
		if (empty($_GET["newTime"])) $_GET["newTime"] = 0;
		db_query("UPDATE helpdesk_tickets SET timeSpent = {$_GET["newTime"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newDepartment"])) {
		db_query("UPDATE helpdesk_tickets SET departmentID = {$_GET["newDepartment"]}, typeID = NULL, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newUser"])) {
		db_query("UPDATE helpdesk_tickets SET created_user = {$_GET["newUser"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	}
	url_query_drop("ticketID, newOwner, newStatus, newPriority, newDepartment, newType");
} else {
	//load dropdown values -- owner, status, priority, department, type
	$ownerOptions = array();
	$result = db_query("SELECT 
			u.user_id, 
			ISNULL(u.nickname, u.firstname) first 
			FROM users u
			LEFT JOIN users_to_modules a ON a.user_id = u.user_id 
			WHERE 
				u.is_active = 1 AND
				( a.module_id = 3 OR u.is_admin = 1 ) 
				AND
				u.departmentID = $departmentID
			ORDER BY first");
	while ($r = db_fetch($result)) $ownerOptions[$r["user_id"]] = $r["first"];
	
	$statusOptions = array();
	$result = db_query("SELECT id, description FROM helpdesk_tickets_statuses");
	while ($r = db_fetch($result)) if ($r["id"] != 9) $statusOptions[$r["id"]] = $r["description"];
	
	$result = db_query("SELECT id, description FROM helpdesk_tickets_priorities");
	while ($r = db_fetch($result)) $priorityOptions[$r["id"]] = $r["description"];

	$result = db_query("SELECT departmentID, shortName FROM departments WHERE isHelpdesk = 1");
	while ($r = db_fetch($result)) $departmentOptions[$r["departmentID"]] = $r["shortName"];
	
	$result = db_query("SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = $departmentID ORDER BY description");
	while ($r = db_fetch($result)) $typeOptions[$r["id"]] = $r["description"];
}

//custom functions

function drawNavigationHelpdesk() {
	global $helpdeskOptions;
	$pages = array("/helpdesk/"=>"All");
	foreach ($helpdeskOptions as $option) {
		$pages["/helpdesk/?dept=" . $option["id"]] = $option["name"];
	}
	return drawNavigationRow($pages, "helpdesk", true);
}

function drawTicketFilter() {
	global $total, $default, $pageName, $_GET;
	$target = (isset($_GET["id"])) ? $pageName . "?id=" . $_GET["id"] : $pageName . "?";
	$return = '
	<script language="javascript">
		<!--
		function goToMonth(str) {
			if (str == "") {
				location.href=\'' . $target . '\';
			} else {
				arr = str.split("/");
				location.href=\'' . $target . '&month=\' + arr[0] + \'&year=\' + arr[1];
			}
			return true;
		}
		//-->
	</script>
	
	<table class="message">
		<tr>
			<td class="gray">
			<table cellpadding="0" cellspacing="0" border="0" align="center">
				<tr style="background-color:transparent;vertical-align:middle;color:#333333;font-size:12px;"><td>
				Filter by month:&nbsp;</td><td>' . draw_form_select_month("month", $total["month"] . "/" . $total["year"], $default, false, "sleek", "goToMonth(this.value)", true) . '</td>
			</td></tr></table>
		</tr>
	</table>';
	return $return;
}

function drawTicketHeader() {
	$return = '
	<tr>
		<th align="left">User</th>
		<th align="left" width="100">Priority</th>
		<th align="left" width="200">Status</th>
		<th align="left" width="100">Assigned To</th>
		<th align="left" width="16"></th>
	</tr>';
	return $return;
}

function drawTicketRow($r, $mode="status") { //mode can be status or type
	global $priorityOptions, $statusOptions, $ownerOptions, $typeOptions, $request, $colors, $_josh["write_folder"];
	$return  = '
	<tr>
		<td rowspan="2">' . drawName($r["created_user"], $r["first"] . ' ' . $r["last"], $r["created_date"], true) . '</td>
		<td colspan="3"><a href="ticket.php?id=' . $r["id"] . '"><b>' . $r["title"] . '</b></a></td>
		<td rowspan="2">' . draw_img($_josh["write_folder"] . "images/icons/delete.gif", deleteLink("Delete this ticket?", $r["id"], "delete", "ticketID")) . '</td>
	</tr>
	<tr>';
	$t = array("ticketID"=>$r["id"]);
	if ($mode == "status") {
		$return .= '<td>' . draw_form_select("", $priorityOptions, $r["priorityID"], false, "field", "location.href='" . url_query_add($t, false) . "&newPriority=' + this.value") . '</td>
			<td>' . draw_form_select("", $statusOptions, $r["statusID"], true, "field", "location.href='" . url_query_add($t, false) . "&newStatus=' + this.value") . '</td>
			<td>' . draw_form_select("", $ownerOptions, $r["ownerID"], false, "field", "location.href='" . url_query_add($t, false) . "&newOwner=' + this.value") . '</td>';
	} elseif ($mode == "type") {
		$return .= '<td colspan="3">' . draw_form_select("", $typeOptions, $r["typeID"], false, "field", "location.href='" . url_query_add($t, false) . "&newType=' + this.value") . '</td>';
	}
	$return .= '</tr>';
	return $return;
}

function emailITticket($id, $scenario, $admin=false) {
	global $_SESSION, $_josh, $module_admin;
	$message  = drawEmailHeader();
	
	$ticket = db_grab("SELECT
			u.user_id,
			(SELECT COUNT(*) FROM users_to_modules a WHERE a.user_id = u.user_id AND a.module_id = 3) isUserAdmin,
			t.title,
			t.created_user,
			t.description,
			t.departmentID,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.email,
			t.created_date,
			t.priorityID,
			t.statusID,
			d.shortName department,
			t.typeID,
			y.description type,
			u2.email as ownerEmail,
			t.ownerID,
			ISNULL(u2.nickname, u2.firstname) as ownerName
		FROM helpdesk_tickets t
		LEFT  JOIN helpdesk_tickets_types y	ON t.typeID		= y.id
		JOIN users  u	ON t.created_user	= u.user_id
		JOIN departments d ON t.departmentID = d.departmentID
		LEFT  JOIN users  u2	ON t.ownerID	= u2.user_id
		WHERE t.id = " . $id);
		
	//yellow box
	if ($scenario == "followup") {
		$subject = "Followup On Your Helpdesk Ticket";
		$message .= drawMessage("There's been followup on your Helpdesk ticket - please see below.  <b>Don't reply to this email!</b>  Instead, please <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view your ticket</a> in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your 'safe senders list,' pictures will always download.");
	} elseif ($scenario == "followupadmin") {
		$subject = "Admin Followup on Helpdesk Ticket";
		$message .= drawMessage("<a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $_SESSION["user_id"] . "'>" . $_SESSION["full_name"] . "</a> just made an administrative followup on this Helpdesk ticket.  Regular staff were not copied on this message.");
	} elseif ($scenario == "closed") {
		$subject = "Your Ticket Has Been Closed";
		$message .= drawMessage("This is to let you know that your ticket has been closed.  <b>Don't reply to this email!</b>  You can still followup on this thread by <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>viewing your ticket</a> in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your 'safe senders list,' pictures will always download.");
	} elseif ($scenario == "assign") {
		$subject = "Your Ticket Has Been Assigned";
		$message .= drawMessage("<a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $_SESSION["user_id"] . "'>" . $_SESSION["full_name"] . "</a> has assigned this ticket to <a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $ticket["ownerID"] . "'>" . $ticket["ownerName"] . "</a>.  <b>Don't reply to this email!</b>  Instead, please <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view your ticket</a> in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your 'safe senders list,' pictures will always download.");
	} elseif ($scenario == "new") {
		$subject = "New " . $ticket["department"] . " Ticket Posted";
		$message .= drawMessage("This is to let you know that a new ticket has just been posted to the Helpdesk.  You can <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view the ticket</a> in the intranet ticketing system.");
	} elseif ($scenario == "critical") {
		$subject = "Critical " . $ticket["department"] . " Ticket Still Open";
		$message .= drawMessage("A ticket flagged \"Critical\" is open on the Helpdesk.  You can <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view the ticket</a> in the intranet ticketing system.");
	} elseif ($scenario == "languishing") {
		$subject = $ticket["department"] . " Ticket Languishing on the Helpdesk";
		$message .= drawMessage("This ticket has been open on the Helpdesk for at least five days now.  You can <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view the ticket</a> in the intranet ticketing system.");
	}

	$message .= '<table class="center">' . drawHeaderRow("Email", 2);
	
	//recipients arrays
	$users = array();
	$admins = array();
	
	if ($ticket["isUserAdmin"]) {
		$admins[] = $ticket["email"];
	} else {
		$users[] = $ticket["email"];
	}
	
	if ($module_admin) {
		$admins[] = $_SESSION["email"];
	} else {
		$users[] = $_SESSION["email"];
	}
	
	//add owner if ticket is assigned
	if ($ticket["ownerEmail"]) $admins[] = $ticket["ownerEmail"]; //owner logically has to be admin

	$message .= drawThreadTop($ticket["title"], $ticket["description"], $ticket["user_id"], $ticket["first"] . " " . $ticket["last"], $ticket["created_date"]);
	
	//second message for the admins -- this seems overly complicated!
	$admin_message = $message;
	
	//get followups
	$followups = db_query("SELECT
			u.user_id,
			f.message,
			(SELECT COUNT(*) FROM users_to_modules a WHERE a.user_id = u.user_id AND a.module_id = 3) isUserAdmin,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.email,
			f.created_date,
			f.is_admin
		FROM helpdesk_tickets_followups f
		INNER JOIN users  u  ON f.created_user	= u.user_id
		WHERE f.ticketID = {$id} ORDER BY f.created_date");
	while ($f = db_fetch($followups)) {
		$admin_message .= drawThreadComment($f["message"], $f["user_id"], $f["first"] . " " . $f["last"], $f["created_date"], $f["is_admin"]);
		if (!$f["is_admin"]) $message .= drawThreadComment($f["message"], $f["user_id"], $f["first"] . " " . $f["last"], $f["created_date"], $f["is_admin"]);
		if ($f["isUserAdmin"]) {
			$admins[] = $f["email"];
		} else {
			$users[] = $f["email"];
		}
	}

	$message		.= '</table>' . drawEmailFooter();
	$admin_message	.= '</table>' . drawEmailFooter();
	
	$admins = array_unique($admins);
	$admins = array_remove($_SESSION["email"], $admins);
	
	$users = array_unique($users);
	$users = array_remove($_SESSION["email"], $users);
		
	//special codes for email
	//todo: put this in db, possibly by adding something to the users table or something
	if (($scenario == "new")			&& ($ticket["departmentID"] == 3)) $admins = array("czanoni@seedco.org","cpena@seedco.org");
	if (($scenario == "new")			&& ($ticket["departmentID"] == 13)) $admins = array("mdavidson@seedco.org","mtorinese@seedco.org");
	if (($scenario == "new")			&& ($ticket["departmentID"] == 2)) $admins = array("smalach@seedco.org");
	if (($scenario == "critical")		&& ($ticket["departmentID"] == 8)) $admins = array("mkhan@seedco.org");

	if (count($admins)) {
		$admins = join(", ", $admins);
		email($admins, $admin_message, $subject);
	}
	
	if (count($users) && ($scenario != "followupadmin") && !$admin) {
		$users = join(", ", $users);
		email($users, $message, $subject);
	}
	//exit;
}
?>