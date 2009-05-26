<? include("../include.php");

$return = '<table width="100%" border="1">
	<tr bgcolor="#EEEEEE">
		<td>Funder</td>
		<td>Award</td>
		<td>Status</td>
		<td>Amount</td>
		<td>Type</td>
		<td>Program</td>
		<td>Start</td>
		<td>End</td>
		<td>Contact</td>
	</tr>';
	$result = db_query("select
							a.funderID,
							f.name,
							a.awardID,
							a.awardTitle,
							s.awardStatusDesc,
							a.awardAmount,
							at.awardTypeDesc,
							p.programDesc,
							a.awardStartDate,
							a.awardEndDate,
							ISNULL(u.nickname, u.firstname) + ' ' + u.lastname contact
							FROM funders_awards a
							LEFT JOIN funders f on f.funderID = a.funderID
							LEFT JOIN funders_awards_types at on a.awardTypeID = at.awardTypeID
							LEFT JOIN funders_programs p on a.awardprogramID = p.programID
							LEFT JOIN funders_awards_statuses s on a.awardStatusID = s.awardStatusID
							LEFT JOIN users u ON u.id = a.staffID");
while ($r = db_fetch($result)) {
	$return .= '
	<tr bgcolor="#FFFFFF" valign="top">
		<td><a href="http://' . $_josh["request"]["host"] . '/funders/funder_view.php?id=' . $r["funderID"] . '">' . $r["name"] . '</a></td>
		<td><a href="http://' . $_josh["request"]["host"] . '/funders/award_view.php?id=' . $r["awardID"] . '">' . $r["awardTitle"] . '</a></td>
		<td>' . $r["awardStatusDesc"] . '</td>
		<td>' . number_format($r["awardAmount"]) . '</td>
		<td>' . $r["awardTypeDesc"] . '</td>
		<td>' . $r["programDesc"] . '</td>
		<td>' . format_date_excel($r["awardStartDate"]) . '</td>
		<td>' . format_date_excel($r["awardEndDate"]) . '</td>
		<td>' . $r["contact"] . '</td>
	</tr>';
	}
$return .= '</table>';

file_download($return, "All Awards - " . date("m/d/y"), "xls");
