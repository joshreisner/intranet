<?
include("../../include.php");

$return = '
<table width="100%" cellpadding="3" cellspacing="1" border="1">
	<tr class="helptext" bgcolor="#CCCCCC">
		<td>Due Date</td>
		<td>Funder / Award</td>
		<td>Report</td>
		<td>Staff</td>
		<td>Status</td>
	</tr>';

	$result = db_query("SELECT
			an.awardID, 
			an.activityTitle,
			an.activityText,
			an.activityDate,
			u.lastname,
			a.awardTitle,
			f.name,
			f.funderID
		FROM funders_activity an
		INNER JOIN users     u ON an.activityAssignedTo = u.id
		INNER JOIN funders_awards   a ON an.awardID = a.awardID
		INNER JOIN funders  f ON a.funderID = f.funderID
		WHERE an.isReport = 1 AND an.isComplete = 0
		ORDER BY activityDate");
while ($r = db_fetch($result)) {
	$date = ($r["activityDate"]) ? date("M j, Y", strtotime($r["activityDate"])) : "N/A";
	$return .= '<tr class="helptext';
	if($r["statusDesc"] == "Overdue") $return .= '-b';
	$return .= '" bgcolor="#FFFFFF" valign="top">
		<td><nobr>' . $date . '</nobr></td>
		<td><a href="http://' . $_josh["request"]["host"] . '/programs/resources_funder_view.php?id=' . $r["funderID"] . '">' . $r["name"] . '</a> /<br><a href="http://' . $_josh["request"]["host"] . '/programs/resources_award_view.php?id=' . $r["awardID"] . '">' . $r["awardTitle"] . '</a></td>
		<td>' . $r["activityTitle"] . '</td>
		<td>' . $r["lastname"] . '</td>
		<td>' . $r["activityText"] . '</td>
	</tr>';
}
$return .= '</table>';

file_download($return, "Report Due Dates - " . date("m/d/y"), "xls");