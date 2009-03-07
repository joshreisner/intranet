<?  
include("../include.php");

$bgcolors[1] = "#FF99CC";
$bgcolors[2] = "#FFFF99";
$bgcolors[5] = "#CCFFFF";

$amtdesc[1]  = "Amt. Awarded";
$amtdesc[2]  = "Amt. Requested";
$amtdesc[5]  = "Amt. Sought";

$grantcycle[1] = "Grant Cycle";
$grantcycle[2] = "Past Action";
$grantcycle[5] = "Past Action";

$return = '<table width="100%" cellpadding="3" cellspacing="1" border="1" bgcolor="#EEEEEE" class="small">';

	$result_programs = db_query("SELECT p.programID, p.programDesc,
								(SELECT count(*) FROM resources_awards a 
								WHERE a.awardProgramID = p.programID AND 
									(awardStatusID = 1 OR awardStatusID = 2 OR awardStatusID = 5)) as awardCount
								FROM intranet_programs p ORDER BY programDesc");
	while ($rp = db_fetch($result_programs)) {
		if (!$rp["awardCount"]) continue;
	$return .= '
	<tr class="bold" bgcolor="#CCCCCC">
		<td colspan="6" height="40px" valign="bottom"><br><br><b><font size="+1">' . $rp["programDesc"] . '</b></font></td>
	</tr>';
	$result_statuses = db_query("SELECT s.awardStatusID, s.awardStatusDescPlural,
										(SELECT count(*) FROM resources_awards a WHERE (a.awardProgramID = " . $rp["programID"] . " OR a.awardProgramID2 = " . $rp["programID"] . ") AND a.awardStatusID = s.awardStatusID) as awardCount
										FROM Resources_Awards_Statuses s
										WHERE awardStatusID = 1 OR awardStatusID = 2 OR awardStatusID = 5");
		while ($rs = db_fetch($result_statuses)) {
			if (!$rs["awardCount"]) continue;
			$return .= '
			<tr>
				<td bgcolor="' . $bgcolors[$rs["awardStatusID"]] . '" colspan="6"><i>' . $rs["awardStatusDescPlural"] . '</i></td>
			</tr>
			<tr>
				<td><font size="-1"><b>Funder</b></font></td>
				<td><font size="-1"><b>Title</b></font></td>
				<td><font size="-1"><b>' . $grantcycle[$rs["awardStatusID"]] . '</b></font></td>
				<td><font size="-1"><b>Status / Next Step</b></font></td>
				<td align="right"><font size="-1"><b>' . $amtdesc[$rs["awardStatusID"]] . '</b></font></td>
				<td><font size="-1"><b>Staff Contact</b></font></td>
			</tr>';
			$result = db_query("SELECT
						f.funderID,
						f.name,
						a.awardID,
						a.awardTitle,
						a.awardAmount,
						u.lastname last_name,
						a.awardStartDate,
						a.awardEndDate,
						(select top 1 activityTitle from resources_activity where awardID = a.awardID and isComplete = 0 order by activityDate) as activityTitle,
						(select top 1 activityDate from resources_activity where awardID = a.awardID and isComplete = 0 order by activityDate) as activityDate,
						(select top 1 activityTitle from resources_activity where awardID = a.awardID and isComplete = 1 order by activityDate desc) as pastActivityTitle,
						(select top 1 activityDate from resources_activity where awardID = a.awardID and isComplete = 1 order by activityDate desc) as pastActivityDate
					FROM resources_awards a 
					INNER JOIN resources_funders  f ON f.funderID = a.funderID
					INNER JOIN users     u ON u.userID = a.staffID
					WHERE a.awardStatusID = " . $rs["awardStatusID"] . " AND a.awardProgramID = " . $rp["programID"]);
				while ($r = db_fetch($result)) {
					$return .= '
				<tr bgcolor="#FFFFFF" class="helptext">
					<td><a href="http://' . $_josh["request"]["host"] . '/funders/funder_view.php?id=' . $r["funderID"] . '">' . $r["name"] . '</a></td>
					<td><a href="http://' . $_josh["request"]["host"] . '/funders/award_view.php?id=' . $r["awardID"] . '">' . $r["awardTitle"] . '</a></td>';
					if ($rs["awardStatusID"] == 1) {
						$return .= '<td>' . date("M, Y", strToTime($r["awardStartDate"])) . ' - ' . date("M, Y", strToTime($r["awardEndDate"])) . '</td>';
					} else {
						$return .= '<td>';
						if ($r["pastActivityTitle"]) {
							$return .= $r["pastActivityTitle"] . ' (' . format_date($r["pastActivityDate"]) . ')';
						}
						$return .= '</td>';
					}
					$return .= '<td>';
					if ($r["activityTitle"]) $return .= $r["activityTitle"] . ' (' . format_date($r["activityDate"]) . ')';
					$return .= '</td>
					<td align="right">$' . number_format($r["awardAmount"]) . '</td>
					<td>' . $r["last_name"] . '</td>
				</tr>';
			}
			
			$result = db_query("SELECT
						f.funderID,
						f.name,
						a.awardID,
						a.awardTitle,
						p.programDesc
					FROM resources_awards a 
					INNER JOIN resources_funders f ON f.funderID = a.funderID
					INNER JOIN intranet_programs p ON a.awardProgramID = p.programID
					WHERE a.awardStatusID = " . $rs["awardStatusID"] . " AND a.awardProgramID2 = " . $rp["programID"]);
				while ($r = db_fetch($result)) {
					$return .= '
				<tr bgcolor="#FFFFFF" class="helptext">
					<td><a href="http://' . $_josh["request"]["host"] . '/funders/funder_view.php?id=' . $r["funderID"] . '">' . $r["name"] . '</a></td>
					<td><a href="http://' . $_josh["request"]["host"] . '/funders/award_view.php?id=' . $r["awardID"] . '">' . $r["awardTitle"] . '</a></td>
					<td colspan="4">(See <i>' . $r["programDesc"] . '</i>)</td>
				</tr>';
			}
		}
	}
$return .= '</table>';

file_download($return, "Big List - " . date("m/d/y"), "xls");
?>