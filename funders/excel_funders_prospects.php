<?php
include("../include.php");

$return = '
<table width="100%" border="1" style="font-family:verdana; font-size:11px; padding:1px; border:1px solid #cccccc;">
	<tr height="24" valign="bottom" style="background-color:#eeeecc; font-weight:bold;">
		<td>Funder</td>
		<td>Project</td>
		<td align="right">Amount</td>
		<td>Status</td>
		<td>Next Steps</td>
		<td>Lead Contact</td>
		<td>Corporation</td>
	</tr>';
		
$result = db_query("SELECT
		a.awardID,
		f.name funder,	
		a.awardTitle award,
		a.awardAmount amount,
		t.awardStatusDesc status,
		(SELECT TOP 1 c.activityTitle FROM resources_activity c WHERE c.awardID = a.awardID AND c.isComplete = 0 AND c.isActionItem = 1 ORDER BY activityDate ASC) nextsteps,
		ISNULL(u.nickname, u.firstname) + ' ' + u.lastname contact
	FROM resources_awards a
	JOIN resources_funders f ON a.funderID = f.funderID
	JOIN resources_awards_statuses t ON a.awardStatusID = t.awardStatusID
	JOIN users u ON a.staffID = u.user_id
	WHERE ((a.awardStatusID = 1) OR (a.awardStatusID = 2) OR (a.awardStatusID = 5))
	ORDER BY funder, award");
while ($r = db_fetch($result)) {
	//$date = ($r["activityDate"]) ? date("M j, Y", strtotime($r["activityDate"])) : "N/A";

$return .= '
	<tr height="18">
		<td>' . $r["funder"] . '</td>
		<td><a href="http://' . $_josh["request"]["host"] . '/funders/award_view.php?id=' . $r["awardID"] . '">' . $r["award"] . '</a></a></td>
		<td align="right">';
if ($r["amount"]) $return .= "$" . number_format($r["amount"]);
$return .= '</td>
		<td>' . $r["status"] . '</td>
		<td>' . $r["nextsteps"] . '</td>
		<td>' . $r["contact"] . '</td>
		<td></td>
	</tr>';
}
$return .= '</table>';

file_download($return, "Funders and Prospects " . date("m/d/y"), "xls");
?>