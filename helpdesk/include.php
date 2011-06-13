<?php include("../include.php");

//kick out user if not administrator ~ should be done with page info
if (!$page['is_admin'] && $page["is_admin"]) url_change("/helpdesk/");

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
			db_query("UPDATE helpdesk_tickets SET closed_date = GETDATE() WHERE id = " . $_GET["ticketID"]);
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
		db_query("UPDATE helpdesk_tickets SET type_id = {$_GET["newType"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newTime"])) {
		if (empty($_GET["newTime"])) $_GET["newTime"] = 0;
		db_query("UPDATE helpdesk_tickets SET timeSpent = {$_GET["newTime"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newDepartment"])) {
		db_query("UPDATE helpdesk_tickets SET departmentID = {$_GET["newDepartment"]}, type_id = NULL, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	} elseif (isset($_GET["newUser"])) {
		db_query("UPDATE helpdesk_tickets SET created_user = {$_GET["newUser"]}, updated_date = GETDATE(), updated_user = {$_SESSION["user_id"]} WHERE id = " . $_GET["ticketID"]);
	}
	url_query_drop("ticketID, newOwner, newStatus, newPriority, newDepartment, newType");
} else {
	//load dropdown values -- owner, status, priority, department, type
	$ownerOptions = array();
	$result = db_query("SELECT 
			u.id, 
			ISNULL(u.nickname, u.firstname) first 
			FROM users u
			LEFT JOIN users_to_modules a ON a.user_id = u.id 
			WHERE 
				u.is_active = 1 AND
				( a.module_id = 3 OR u.is_admin = 1 ) 
				AND
				u.departmentID = $departmentID
			ORDER BY first");
	while ($r = db_fetch($result)) $ownerOptions[$r["id"]] = $r["first"];
	
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
	return draw_javascript('
		function goToMonth(str) {
			if (str == "") {
				location.href=\'' . $target . '\';
			} else {
				arr = str.split("/");
				location.href=\'' . $target . '&month=\' + arr[0] + \'&year=\' + arr[1];
			}
			return true;
		}
	') .  draw_div_class('message', 'Filter by month:&nbsp;' . draw_form_select_month("month", $total["month"] . "/" . $total["year"], $default, false, "sleek", "goToMonth(this.value)", true));
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
	global $priorityOptions, $statusOptions, $ownerOptions, $typeOptions, $_josh;
	$return  = '
	<tr>
		<td rowspan="2">' . drawName($r["created_user"], $r["first"] . ' ' . $r["last"], $r["created_date"], true, $r['updated']) . '</td>
		<td colspan="3"><a href="ticket.php?id=' . $r["id"] . '"><b>' . $r["title"] . '</b></a></td>
		<td rowspan="2">' . draw_img("/images/icons/delete.png", drawDeleteLink("Delete this ticket?", $r["id"], "delete", "ticketID")) . '</td>
	</tr>
	<tr>';
	$t = array("ticketID"=>$r["id"]);
	if ($mode == "status") {
		$return .= '<td>' . draw_form_select("", $priorityOptions, $r["priorityID"], false, "field", "location.href='" . url_query_add($t, false) . "&newPriority=' + this.value") . '</td>
			<td>' . draw_form_select("", $statusOptions, $r["statusID"], true, "field", "location.href='" . url_query_add($t, false) . "&newStatus=' + this.value") . '</td>
			<td>' . draw_form_select("", $ownerOptions, $r["ownerID"], false, "field", "location.href='" . url_query_add($t, false) . "&newOwner=' + this.value") . '</td>';
	} elseif ($mode == "type") {
		$return .= '<td colspan="3">' . draw_form_select("", $typeOptions, $r["type_id"], false, "field", "location.href='" . url_query_add($t, false) . "&newType=' + this.value") . '</td>';
	}
	$return .= '</tr>';
	return $return;
}

function emailITticket($id, $scenario, $admin=false, $debug=false) {
	global $_josh, $page;
	
	$ticket = db_grab('SELECT
			u.id,
			(SELECT COUNT(*) FROM users_to_modules a WHERE a.user_id = u.id AND a.module_id = 3) isUserAdmin,
			t.title,
			t.created_user,
			t.description,
			t.departmentID,
			ISNULL(u.nickname, u.firstname) first,
			u.lastname last,
			u.email,
			' . db_updated('u') . ',
			t.created_date,
			t.priorityID,
			t.statusID,
			d.shortName department,
			t.type_id,
			y.description type,
			u2.email as ownerEmail,
			t.ownerID,
			ISNULL(u2.nickname, u2.firstname) as ownerName
		FROM helpdesk_tickets t
		LEFT JOIN helpdesk_tickets_types y	ON t.type_id = y.id
		JOIN users u ON t.created_user = u.id
		JOIN departments d ON t.departmentID = d.departmentID
		LEFT JOIN users u2 ON t.ownerID = u2.id
		WHERE t.id = ' . $id);
		
	//yellow box
	if ($scenario == "followup") {
		$subject = "Followup On Your Helpdesk Ticket";
		$message = drawMessage('There\'s been followup on your Helpdesk ticket - please see below.  <b>Don\'t reply to this email!</b>  Instead, please ' . draw_link('/helpdesk/ticket.php?id=' . $id, 'view your ticket') . ' in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your "safe senders list," pictures will always download.');
	} elseif ($scenario == "followupadmin") {
		$subject = "Admin Followup on Helpdesk Ticket";
		$message = drawMessage(draw_link('/staff/view.php?id=' . user(), $_SESSION['full_name']) . ' just made an administrative followup on this Helpdesk ticket.  Regular staff were not copied on this message.');
	} elseif ($scenario == "closed") {
		$subject = "Your Ticket Has Been Closed";
		$message = drawMessage('This is to let you know that your ticket has been closed.  <b>Don\'t reply to this email!</b>  You can still followup on this thread by ' . draw_link('/helpdesk/ticket.php?id=' . $id, 'viewing your ticket') . ' in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your "safe senders list," pictures will always download.');
	} elseif ($scenario == "assign") {
		$subject = "Your Ticket Has Been Assigned";
		$message = drawMessage(draw_link('/staff/view.php?id=' . user(), $_SESSION["full_name"]) . ' has assigned this ticket to ' . draw_link('/staff/view.php?id=' . $ticket['ownerID'], $ticket["ownerName"]) . '<b>Don\'t reply to this email!</b>  Instead, please ' . draw_link('/helpdesk/ticket.php?id=' . $id, 'view your ticket') . ' in the intranet ticketing system.<br><br><b>Note:</b> if you add this sender to your "safe senders list," pictures will always download.');
	} elseif ($scenario == "new") {
		$subject = "New " . $ticket["department"] . " Ticket Posted";
		$message = drawMessage('This is to let you know that a new ticket has just been posted to the Helpdesk.  You can ' . draw_link('/helpdesk/ticket.php?id=' . $id, 'view the ticket') . ' in the intranet ticketing system.');
	} elseif ($scenario == "critical") {
		$subject = "Critical " . $ticket["department"] . " Ticket Still Open";
		$message = drawMessage('A ticket flagged "Critical" is open on the Helpdesk.  You can ' . draw_link('/helpdesk/ticket.php?id=' . $id, 'view the ticket') . ' in the intranet ticketing system.');
	}

	//$message .= drawtableStart() . drawHeaderRow(false, 2);
	
	//recipients arrays
	$users = array();
	$admins = array();
	
	if ($ticket["isUserAdmin"]) {
		$admins[] = $ticket["email"];
	} else {
		$users[] = $ticket["email"];
	}
	
	if ($page['is_admin']) {
		$admins[] = $_SESSION["email"];
	} else {
		$users[] = $_SESSION["email"];
	}
	
	//add owner if ticket is assigned
	if ($ticket["ownerEmail"]) $admins[] = $ticket["ownerEmail"]; //owner logically has to be admin

	$d_user	= new display($page['breadcrumbs'] . $ticket['title'], false, false, 'thread');
	$d_admin = new display($page['breadcrumbs'] . $ticket['title'], false, false, 'thread');
	$d_user->row(drawName($ticket['created_user'], $ticket['first'] . ' ' . $ticket['last'], $ticket['created_date'], true, BR, $ticket['updated']), draw_h1($ticket['title']) . $ticket['description']);
	$d_admin->row(drawName($ticket['created_user'], $ticket['first'] . ' ' . $ticket['last'], $ticket['created_date'], true, BR, $ticket['updated']), draw_h1($ticket['title']) . $ticket['description']);
	
	//get followups
	$followups = db_query('SELECT
			u.id,
			f.message,
			(SELECT COUNT(*) FROM users_to_modules u2m WHERE u2m.user_id = u.id AND u2m.module_id = 3 AND u2m.is_admin = 1) isUserAdmin,
			ISNULL(u.nickname, u.firstname) firstname,
			u.lastname,
			u.email,
			f.created_date,
			f.is_admin,
			f.created_user,
			' . db_updated('u') . '
		FROM helpdesk_tickets_followups f
		INNER JOIN users  u  ON f.created_user	= u.id
		WHERE f.ticketID = ' . $id . ' ORDER BY f.created_date');
	while ($f = db_fetch($followups)) {
		$d_admin->row(drawName($f['created_user'], $f['firstname'] . ' ' . $f['lastname'], $f['created_date'], true, BR, $f['updated']), $f['message']);
		if (!$f['is_admin']) $d_user->row(drawName($f['created_user'], $f['firstname'] . ' ' . $f['lastname'], $f['created_date'], true, BR, $f['updated']), $f['message']);
		if ($f['isUserAdmin']) {
			$admins[] = $f['email'];
		} else {
			$users[] = $f['email'];
		}
	}

	$admins	= array_remove($_SESSION['email'], array_unique($admins));
	$users	= array_remove($_SESSION['email'], array_unique($users));
	
	if ($debug) die(drawEmail($message . $d_admin->draw()));
			
	//special codes for email
	//todo: put this in db, possibly by adding something to the users table or something
	if (($scenario == "new")			&& ($ticket["departmentID"] == 3)) $admins = array('linungu@seedco.org', 'lmiura@seedco.org', 'sshah@seedco.org');
	if (($scenario == "new")			&& ($ticket["departmentID"] == 13)) $admins = array('mdavidson@seedco.org', 'mtorinese@seedco.org');
	if (($scenario == "new")			&& ($ticket["departmentID"] == 2)) $admins = array('dsashti@seedco.org', 'mfriend@seedco.org', 'ichangcimino@seedco.org', 'vvilsaint@seedco.org');
	if (($scenario == "critical")		&& ($ticket["departmentID"] == 8)) $admins = array('mkhan@seedco.org');

	if (count($admins)) {
		//$admins = join(", ", $admins);
		email($admins, drawEmail($message . $d_admin->draw()), $subject);
		error_debug('admin message emailed to ' . implode(', ', $admins) . ' admins', __file__, __line__);
	}
	
	if (count($users) && ($scenario != "followupadmin") && !$admin) {
		//$users = join(", ", $users);
		email($users, drawEmail($message . $d_user->draw()), $subject);
		error_debug('user message emailed to ' . implode(', ', $users) . ' users', __file__, __line__);
	}
}
?>