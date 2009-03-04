<?	include("../include.php");

	//bail if no 
	if (!isset($_GET["id"])) url_change("funders.php");
	
	//change activity status
	if (isset($_GET["toggleStatus"])) {
		db_query("UPDATE resources_activity SET isComplete = " . $_GET["toggleStatus"] . " WHERE activityID = " . $_GET["id"]);
		url_change("activity_view.php?id=" . $_GET["id"]);
	}
				
drawTop();


$r = db_grab("SELECT 
				a.activityID, 
				a.funderID, 
				a.awardID, 
				a.activityTitle, 
				a.activityText, 
				a.activityDate, 
				a.activityAssignedTo,
				ISNULL(u2.nickname, u2.firstname) + ' ' + u2.lastname assignedTo,
				a.isActionItem, 
				a.isComplete, 
				a.isReport, 
				a.isInternalDeadline, 
				a.activityPostedOn,
				f.funderID,
				f.name,
				w.awardTitle,
				a.activityPostedBy,
				ISNULL(u.nickname, u.firstname) + ' ' + u.lastname postedBy
			FROM resources_activity a
			INNER JOIN intranet_users     u  ON a.activityPostedBy = u.userID
			INNER JOIN intranet_users     u2 ON a.activityAssignedTo = u2.userID
			INNER JOIN resources_awards   w  ON a.awardID  = w.awardID
			INNER JOIN resources_funders  f  ON f.funderID = w.funderID
			WHERE a.activityID = " . $_GET["id"]);
				
?>


<table cellspacing="1" class="left">
	<?=drawHeaderRow("View Activity", 2);?>
	<tr>
		<td class="gray">Award</td>
		<td><b><a href="award_view.php?id=<?=$r["awardID"]?>"><?=$r["awardTitle"]?></a></b> (awarded by <b><a href="funder_view.php?id=<?=$r["funderID"]?>"><?=$r["name"]?></a></b>)</td>
	</tr>
	<tr>
		<td class="gray">Activity</td>
		<td><b><?=$r["activityTitle"]?></b></td>
	</tr>
	<tr>
		<td class="gray">Date</td>
		<td><?=format_date($r["activityDate"])?></td>
	</tr>
	<tr>
		<td class="gray">Staff Responsible</td>
		<td><a href="/staff/view.php?id=<?=$r["activityAssignedTo"]?>"><?=$r["assignedTo"]?></a></td>
	</tr>
	<tr>
		<td class="gray">Posted</td>
		<td><?=format_date($r["activityPostedOn"])?> by <a href="/staff/view.php?id=<?=$r["activityPostedBy"]?>"><?=$r["postedBy"]?></a></td>
	</tr>
	<tr>
		<td class="gray">Status</td>
		<td>
			<select class="field" onChange="javascript:location.href='<?=$_josh["request"]["path_query"]?>&toggleStatus=' + this.value;">
				<option value="0" <?if(!$r["isComplete"]){?> selected<?}?>>Incomplete</option>
				<option value="1" <?if( $r["isComplete"]){?> selected<?}?>>Complete</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="gray" valign="top" height="80">Notes:</td>
		<td valign="top"><?=nl2br($r["activityText"])?></td>
	</tr>
	<? if ($isAdmin) {?>
	<tr class="gray">
		<td colspan="2" align="center"><?=draw_form_button("edit activity note","activity_edit.php?id=" . $_GET["id"])?></td>
	</tr>
	<? }?>
</table>
<? drawBottom(); ?>