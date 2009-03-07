<?php include("../include.php");

//kick out user if not administrator ~ should be done with page info
if (!$isAdmin && $page["isAdmin"]) url_change("/helpdesk/");

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
	$where = " AND MONTH(t.createdOn) = " . $_GET["month"] . " AND YEAR(t.createdOn) = " . $_GET["year"] . " AND t.departmentID = " . $departmentID;
	$total = db_grab("SELECT 
		(SELECT SUM(t.timeSpent) minutes FROM helpdesk_tickets t WHERE MONTH(t.createdOn) = " . $_GET["month"] . " AND YEAR(t.createdOn) = " . $_GET["year"] . " AND departmentID = " . $departmentID . ") minutes, 
		MONTH(MIN(createdOn)) month,
		YEAR(MIN(createdOn)) year
		FROM helpdesk_tickets WHERE departmentID = " . $departmentID);
} else {
	$where = " AND t.departmentID = " . $departmentID;
	$default = "";
	$total = db_grab("SELECT 
		SUM(timeSpent) minutes, 
		MONTH(MIN(createdOn)) month,
		YEAR(MIN(createdOn)) year
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
        db_query("UPDATE helpdesk_tickets SET ownerID = {$_GET["newOwner"]}, statusID = {$r}, updatedOn  = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
        if ($_GET["newOwner"] != "NULL") emailITticket($_GET["ticketID"], "assign");
	} elseif  (isset($_GET["newStatus"])) {
		if (empty($_GET["newStatus"])) $_GET["newStatus"] = "NULL";
		if ($_GET["newStatus"] == 9) {
			db_query("UPDATE helpdesk_tickets SET closedDate = GETDATE() WHERE id = " . $_GET["ticketID"]);
	        emailITticket($_GET["ticketID"], "closed");
        }
        db_query("UPDATE helpdesk_tickets SET statusID = {$_GET["newStatus"]}, updatedOn  = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif  (isset($_GET["newPriority"])) {
        db_query("UPDATE helpdesk_tickets SET priorityID = {$_GET["newPriority"]}, updatedOn  = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
		//email mohammed for critical
	} elseif (isset($_GET["newDepartment"])) {
		db_query("UPDATE helpdesk_tickets SET departmentID = {$_GET["newDepartment"]}, updatedOn = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newType"])) {
		if (empty($_GET["newType"])) $_GET["newType"] = "NULL";
		db_query("UPDATE helpdesk_tickets SET typeID = {$_GET["newType"]}, updatedOn = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newTime"])) {
		if (empty($_GET["newTime"])) $_GET["newTime"] = 0;
		db_query("UPDATE helpdesk_tickets SET timeSpent = {$_GET["newTime"]}, updatedOn = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newDepartment"])) {
		db_query("UPDATE helpdesk_tickets SET departmentID = {$_GET["newDepartment"]}, typeID = NULL, updatedOn = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newUser"])) {
		db_query("UPDATE helpdesk_tickets SET createdBy = {$_GET["newUser"]}, updatedOn = GETDATE(), updatedBy = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	}
	url_query_drop("ticketID, newOwner, newStatus, newPriority, newDepartment, newType");
} else {
	//load dropdown values -- owner, status, priority, department, type
	$ownerOptions = array();
	$result = db_query("SELECT 
			u.userID, 
			ISNULL(u.nickname, u.firstname) first 
			FROM users u
			LEFT JOIN users_to_modules a ON a.userID = u.userID 
			WHERE 
				u.isActive = 1 AND
				( a.moduleID = 3 OR u.isAdmin = 1 ) 
				AND
				u.departmentID = $departmentID
			ORDER BY first");
	while ($r = db_fetch($result)) $ownerOptions[$r["userID"]] = $r["first"];
	
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
	global $priorityOptions, $statusOptions, $ownerOptions, $typeOptions, $request, $colors, $locale;
	$return  = '
	<tr>
		<td rowspan="2">' . drawName($r["createdBy"], $r["first"] . ' ' . $r["last"], $r["imageID"], $r["width"], $r["height"], $r["createdOn"], true) . '</td>
		<td colspan="3"><a href="ticket.php?id=' . $r["id"] . '"><b>' . $r["title"] . '</b></a></td>
		<td rowspan="2">' . draw_img($locale . "images/icons/delete.gif", deleteLink("Delete this ticket?", $r["id"], "delete", "ticketID")) . '</td>
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
	global $_SESSION, $_josh, $isAdmin;
	$message  = drawEmailHeader();
	
	$ticket = db_grab("SELECT
			u.userID,
			(SELECT COUNT(*) FROM users_to_modules a WHERE a.userID = u.userID AND a.moduleID = 3) isUserAdmin,
			t.title,
			t.createdBy,
			t.description,
			t.departmentID,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.email,
			t.createdOn,
			t.priorityID,
			t.statusID,
			d.shortName department,
			t.typeID,
			y.description type,
			u.imageID,
			m.width,
			m.height,
			u2.email as ownerEmail,
			t.ownerID,
			ISNULL(u2.nickname, u2.firstname) as ownerName
		FROM helpdesk_tickets t
		LEFT  JOIN helpdesk_tickets_types y	ON t.typeID		= y.id
		JOIN users  u	ON t.createdBy	= u.userID
		JOIN departments d ON t.departmentID = d.departmentID
		LEFT  JOIN intranet_images m	ON u.imageID	= m.imageID
		LEFT  JOIN users  u2	ON t.ownerID	= u2.userID
		WHERE t.id = " . $id);
		
	//yellow box
	if ($scenario == "followup") {
		$subject = "Followup On Your Helpdesk Ticket";
		$message .= drawServerMessage("There's been followup on your Helpdesk ticket - please see below.  <b>Don't reply to this email!</b>  Instead, please <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view your ticket</a> in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your 'safe senders list,' pictures will always download.");
	} elseif ($scenario == "followupadmin") {
		$subject = "Admin Followup on Helpdesk Ticket";
		$message .= drawServerMessage("<a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $_SESSION["user_id"] . "'>" . $_SESSION["full_name"] . "</a> just made an administrative followup on this Helpdesk ticket.  Regular staff were not copied on this message.");
	} elseif ($scenario == "closed") {
		$subject = "Your Ticket Has Been Closed";
		$message .= drawServerMessage("This is to let you know that your ticket has been closed.  <b>Don't reply to this email!</b>  You can still followup on this thread by <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>viewing your ticket</a> in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your 'safe senders list,' pictures will always download.");
	} elseif ($scenario == "assign") {
		$subject = "Your Ticket Has Been Assigned";
		$message .= drawServerMessage("<a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $_SESSION["user_id"] . "'>" . $_SESSION["full_name"] . "</a> has assigned this ticket to <a href='http://" . $_josh["request"]["host"] . "/staff/view.php?id=" . $ticket["ownerID"] . "'>" . $ticket["ownerName"] . "</a>.  <b>Don't reply to this email!</b>  Instead, please <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view your ticket</a> in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your 'safe senders list,' pictures will always download.");
	} elseif ($scenario == "new") {
		$subject = "New " . $ticket["department"] . " Ticket Posted";
		$message .= drawServerMessage("This is to let you know that a new ticket has just been posted to the Helpdesk.  You can <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view the ticket</a> in the intranet ticketing system.");
	} elseif ($scenario == "critical") {
		$subject = "Critical " . $ticket["department"] . " Ticket Still Open";
		$message .= drawServerMessage("A ticket flagged \"Critical\" is open on the Helpdesk.  You can <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view the ticket</a> in the intranet ticketing system.");
	} elseif ($scenario == "languishing") {
		$subject = $ticket["department"] . " Ticket Languishing on the Helpdesk";
		$message .= drawServerMessage("This ticket has been open on the Helpdesk for at least five days now.  You can <a href='http://" . $_josh["request"]["host"] . "/helpdesk/ticket.php?id=" . $id . "'>view the ticket</a> in the intranet ticketing system.");
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
	
	if ($isAdmin) {
		$admins[] = $_SESSION["email"];
	} else {
		$users[] = $_SESSION"email"];
	}
	
	//add owner if ticket is assigned
	if ($ticket["ownerEmail"]) $admins[] = $ticket["ownerEmail"]; //owner logically has to be admin

	$message .= drawThreadTop($ticket["title"], $ticket["description"], $ticket["userID"], $ticket["first"] . " " . $ticket["last"], $ticket["imageID"], $ticket["width"], $ticket["height"], $ticket["createdOn"]);
	
	//second message for the admins -- this seems overly complicated!
	$admin_message = $message;
	
	//get followups
	$followups = db_query("SELECT
			u.userID,
			f.message,
			(SELECT COUNT(*) FROM users_to_modules a WHERE a.userID = u.userID AND a.moduleID = 3) isUserAdmin,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.email,
			f.createdOn,
			u.imageID,
			m.width,
			m.height,
			f.isAdmin
		FROM helpdesk_tickets_followups f
		INNER JOIN users  u  ON f.createdBy	= u.userID
		LEFT  JOIN intranet_images m  ON u.imageID		= m.imageID
		WHERE f.ticketID = {$id} ORDER BY f.createdOn");
	while ($f = db_fetch($followups)) {
		$admin_message .= drawThreadComment($f["message"], $f["userID"], $f["first"] . " " . $f["last"], $f["imageID"], $f["width"], $f["height"], $f["createdOn"], $f["isAdmin"]);
		if (!$f["isAdmin"]) $message .= drawThreadComment($f["message"], $f["userID"], $f["first"] . " " . $f["last"], $f["imageID"], $f["width"], $f["height"], $f["createdOn"], $f["isAdmin"]);
		if ($f["isUserAdmin"]) {
			$admins[] = $f["email"];
		} else {
			$users[] = $f["email"];
		}
	}

	$message		.= '</table>' . drawEmailFooter();
	$admin_message	.= '</table>' . drawEmailFooter();
	
	$headers  = "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
	$headers .= "From: Intranet <donotreply@seedco.org>\r\n";

	$admins = array_unique($admins);
	$admins = array_remove($_SESSION["email"], $admins);
	
	$users = array_unique($users);
	$users = array_remove($_SESSION["email"], $users);
		
	//special codes for email
	if (($scenario == "new")			&& ($ticket["departmentID"] == 3)) $admins = array("czanoni@seedco.org","cpena@seedco.org");
	if (($scenario == "new")			&& ($ticket["departmentID"] == 13)) $admins = array("mdavidson@seedco.org","mtorinese@seedco.org");
	if (($scenario == "new")			&& ($ticket["departmentID"] == 2)) $admins = array("smalach@seedco.org");
	if (($scenario == "critical")		&& ($ticket["departmentID"] == 8)) $admins = array("mkhan@seedco.org");

	if (count($admins)) {
		$admins = join(", ", $admins);
		mail($admins, $subject, $admin_message, $headers);
		//echo "Sending admin email to " . $admins;
	}
	
	if (count($users) && ($scenario != "followupadmin") && !$admin) {
		$users = join(", ", $users);
		mail($users, $subject, $message, $headers);
		//echo "Sending user email to " . $users;
	}
	//exit;
}
?>