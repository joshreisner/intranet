<?php
include("include.php");

$report = array();
$result = db_query("SELECT 
		ISNULL(u.nickname, u.firstname) 'Helpdesk Admin',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.id AND " . db_datediff("t.created_date", "t.closed_date") . " <= 1) 'Under Day',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.id AND " . db_datediff("t.created_date", "t.closed_date") . " <= 7 AND " . db_datediff("t.created_date", "t.closed_date") . " > 1) 'Under Week',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.id AND " . db_datediff("t.created_date", "t.closed_date") . " <= 30 AND " . db_datediff("t.created_date", "t.closed_date") . " > 7) 'Under Month',
		(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.id AND " . db_datediff("t.created_date", "t.closed_date") . " > 30) 'Over Month'
	FROM users u
	WHERE u.departmentid = 8 AND ((SELECT COUNT(*) FROM users_to_modules a WHERE a.module_id = 3 AND a.user_id = u.id) > 0)
	ORDER BY ISNULL(u.nickname, u.firstname)");
while ($r = db_fetch($result)) $report[] = $r;

echo file_array($report, "Admins by Age");

?>