<? include("../../include.php");

echo drawTop();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Reports", 4)?>
	<tr>
		<th align="left" width="40%">Funder / Award</th>
		<th align="right">Start / End Date</th>
		<!--<th align="left" width="20%">Assigned To</th>-->
		<th align="left" width="20%">Activity</th>
		<th align="right">Due Date</th>
	</tr>
	<?
	//not sure why, but activityAssignedTo is returning empty on first row (sara johnston)
	//commenting out, bc don't think it's worth fixing right now
	$result = db_query("SELECT
	f.funderID,
	f.name,
	a.awardID,
	a.awardTitle,
	a.awardStartDate,
	a.awardEndDate,
	(SELECT TOP 1 c.activityAssignedTo FROM funders_activity c WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 AND isComplete = 0 ORDER BY activityDate ASC) activityAssignedTo,
	(SELECT TOP 1 ISNULL(u.nickname, u.firstname) + ' ' + u.lastname FROM users u JOIN funders_activity c ON c.activityAssignedTo = u.id WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 ORDER BY activityDate ASC) activityAssignedName,
	(SELECT TOP 1 c.activityDate       FROM funders_activity c WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 AND isComplete = 0 ORDER BY activityDate ASC) activityDate,
	(SELECT TOP 1 c.activityTitle      FROM funders_activity c WHERE c.awardID = a.awardID AND isActionItem = 1 AND isInternalDeadline = 0 AND isComplete = 0 ORDER BY activityDate ASC) activityTitle
		FROM funders f
		JOIN funders_awards a on f.funderID = a.funderID
		WHERE f.funderTypeID <> 7 and awardstatusID = 1
		ORDER BY f.name, a.awardTitle");
	while ($r = db_fetch($result)) {?>
	<tr>
		<td><a href="funder_view.php?id=<?=$r["funderID"]?>"><?=$r["name"]?></a> / <br>
			<a href="award_view.php?id=<?=$r["awardID"]?>"><?=$r["awardTitle"]?></a></td>
		<td align="right"><?=format_date($r["awardStartDate"]);?><br><?=format_date($r["awardEndDate"]);?></td>
		<!--<td><a href="/staff/view.php?id=<?=$r["activityAssignedTo"]?>"><?=$r["activityAssignedName"]?></a></td>-->
		<td><?=$r["activityTitle"]?></td>
		<td align="right"><?=format_date($r["activityDate"], false, "", "");?></td>
	</tr>
	<? }?>
</table>
<?=drawBottom();?>