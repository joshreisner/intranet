<?php
include("include.php");

$report = array();
$result = db_query("SELECT 
		ISNULL(u.nickname, u.firstname) 'Helpdesk Admin',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND " . db_datediff("t.createdOn", "t.closeddate") . " <= 1) 'Under Day',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND " . db_datediff("t.createdOn", "t.closeddate") . " <= 7 AND " . db_datediff("t.createdOn", "t.closeddate") . " > 1) 'Under Week',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND " . db_datediff("t.createdOn", "t.closeddate") . " <= 30 AND " . db_datediff("t.createdOn", "t.closeddate") . " > 7) 'Under Month',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND " . db_datediff("t.createdOn", "t.closeddate") . " > 30) 'Over Month'
	FROM intranet_users u
	WHERE u.departmentid = 8 AND ((SELECT COUNT(*) FROM administrators a WHERE a.moduleId = 3 AND a.userID = u.userID) > 0)
	ORDER BY ISNULL(u.nickname, u.firstname)");
while ($r = db_fetch($result)) $report[] = $r;

echo file_array($report, "Admins by Age");

?>