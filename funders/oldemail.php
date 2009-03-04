<?
include("../include.php");
drawTop();

$to       = "zmabel@seedco.org, jreisner@seedco.org";
$subject  = "To-Do List $month/$today";
$headers  = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
$headers .= "From: " . $_josh["email_default"] . "\r\n";
$message  = drawEmailHeader() . '
	
			<table width="100%" cellpadding="2" cellspacing="1" border="0" bgcolor="#D72226">
				<tr bgcolor="#FFEEEE" class="helptext">
					<td align="center" width="25%"><a href="http://' . $_josh["request"]["host"] . '/funders/">Funders</a></td>
					<td align="center" width="25%"><a href="http://' . $_josh["request"]["host"] . '/funders/programs.php">Programs</a></td>
					<td align="center" width="25%"><a href="http://' . $_josh["request"]["host"] . '/funders/staff.php">Staff</a></td>
					<td align="center" width="25%"><a href="http://' . $_josh["request"]["host"] . '/funders/reports.php">Reports</a></td>
				</tr>
			</table>
			<br>

			<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE" class="small">
				' . drawHeaderRow($subject, 3) . '
				<tr bgcolor="#F6F6F6">
					<td width="20%">Person / Date</td>
					<td width="65%">Funder / Award / Activity</td>
					<td width="15%">Type</td>
				</tr>
';

	$result = db_query("SELECT
			a.activityID,
			ra.funderID,
			f.name,
			a.awardID,
			ra.awardTitle,
			a.activityTitle,
			a.activityDate,
			ISNULL(u.nickname, u.firstname) + ' ' + u.lastname username,
			a.activityText,
			a.isComplete,
			a.isReport,
			a.isActionItem,
			a.isInternalDeadline
		FROM resources_activity a
		INNER JOIN intranet_users     u ON a.activityAssignedTo = u.userid
		INNER JOIN resources_awards  ra ON a.awardID = ra.awardID
		INNER JOIN resources_funders  f ON ra.funderID = f.funderID
		WHERE activityDate < GETDATE() AND isComplete = 0
		ORDER BY u.lastname, a.activityDate");
		
	while ($r = db_fetch($result)) {
		$bgcolor = (!$r["isInternalDeadline"] && !$r["isComplete"]) ? "FFEEEE" : "FFFFFF";
		if (!$r["isActionItem"]) {
			$status = "Activity Note";
		} else {
			$status  = ($r["isInternalDeadline"]) ? "Internal"   : "External";
			$status .= ($r["isReport"])           ? " report"    : " deadline";
			$status .= ($r["isComplete"])         ? ", complete" : ", incomplete";
		}
		$link = ($r["funderID"]) ? "funder_view.php?id=" . $r["funderID"] : "award_view.php?id=" . $r["awardID"];
		$message .='
			<tr class="helptext" bgcolor="' . $bgcolor . '" valign="top">
				<td><b><nobr>' . $r["username"] . '&nbsp;&nbsp;</nobr></b><br><nobr>' . format_date($r["activityDate"]). '</nobr>&nbsp;</td>
				<td><b><a href="http://' . $_josh["request"]["host"] . '/funders/funder_view.php?id=' . $r["funderID"] . '">' . $r["name"]. '</a> - <a href="http://' . $_josh["request"]["host"] . '/funders/award_view.php?id=' . $r["awardID"] . '">' . $r["awardTitle"]. '</a> - ' . $r["activityTitle"]. '</b><br>' . $r["activityText"]. '</td>
				<td>' . $status. '</td>
			</tr>
		';
	}
	
	$message.= '</table>' . drawEmailFooter();

//echo $message;
mail($to, $subject, $message, $headers);
?>

email sent

<?
drawBottom();
?>
