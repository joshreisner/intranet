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
	$columns[] = "(SELECT COUNT(*) FROM helpdesk_tickets t WHERE t.typeID = y.id AND MONTH(t.created_date) = $thismonth AND YEAR(t.created_date) = $thisyear) '" . $mos[$thismonth - 1] . " " . $thisyear . "'";

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
$result = db_query("SELECT y.description, " . implode(", ", $columns) . " FROM helpdesk_tickets_types y WHERE y.departmentID = $departmentID ORDER BY y.description");
while ($r = db_fetch($result)) $report[] = $r;

echo file_array($report, "Types Report");

?>