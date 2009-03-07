<?php
include("include.php");

//initialize variables
$report		= array();
$columns	= array();

$break		= false;
$thismonth	= $total["month"];
$thisyear	= $total["year"];

//loop through
while (!$break) {
	$columns[] = "(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.ownerID = u.userID AND MONTH(t.createdOn) = $thismonth AND YEAR(t.createdOn) = $thisyear) '" . $mos[$thismonth - 1] . " " . $thisyear . "'";

	if (($thismonth == $month) && ($thisyear == $year)) { //if we're up to the current month & year, break
		$break = true;
	} else { //otherwise increment for next loop
		if ($thismonth == 12) {
			$thismonth = 1;
			$thisyear++;
		} else {
			$thismonth++;
		}
	}
}

//execute query we just built
$result = db_query("SELECT ISNULL(u.nickname, u.firstname) 'Helpdesk Admin', " . implode(", ", $columns) . " FROM users u WHERE u.departmentID = $departmentID AND (SELECT COUNT(*) FROM users_to_modules a WHERE a.userID = u.userID AND a.moduleID = 3) > 0 ORDER BY ISNULL(u.nickname, u.firstname)");
while ($r = db_fetch($result)) $report[] = $r;

echo file_array($report, "Admins Report");

?>