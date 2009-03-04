<?	include("../include.php");

if (!isset($_GET["id"])) url_change("/funders/");

if (!empty($_POST)) {
	$isActionItem = (isset($_POST["chkActionItem"])) ? 1 : 0;
	$isReport     = (isset($_POST["chkReport"]))     ? 1 : 0;
	$isInternal   = ($_POST["isInternal"] == "true") ? 1 : 0;

	db_query("UPDATE resources_activity SET 
		activityTitle      = '" . $_POST["activityTitle"] . "',
		activityDate       = '" . format_date_sql($_POST["activityDateMonth"], $_POST["activityDateDay"], $_POST["activityDateYear"]) . "',
		activityAssignedTo = '" . $_POST["activityAssignedTo"] . "',
		isComplete         = '" . $_POST["isComplete"] . "',
		isActionItem       = $isActionItem,
		isReport           = $isReport,
		isInternalDeadline = $isInternal,			
		activityText       = '" . $_POST["activityText"] . "'
		WHERE activityID   =  " . $_GET["id"]);
	url_change("activity_view.php?id=" . $_GET["id"]);
}

drawTop();


$r = db_grab("SELECT 
				a.activityID, 
				a.funderID, 
				f.name,
				w.awardTitle,
				a.awardID, 
				a.activityTitle, 
				a.activityText, 
				a.activityDate, 
				a.activityAssignedTo, 
				a.isActionItem, 
				a.isComplete, 
				a.isReport, 
				a.isInternalDeadline, 
				a.activityPostedOn
			FROM resources_activity a
			INNER JOIN resources_awards w  ON a.awardID = w.awardID
			INNER JOIN resources_funders f ON w.funderID = f.funderID
			WHERE activityID = " . $_GET["id"]);
	?>

	<script language="javascript">
	<!--
	function checkInternal() {
		if (document.frmActivity.chkActionItem.checked) {
			document.all["internal"].style.visibility = "visible";
		} else {
			document.all["report"].style.visibility = "hidden";
			document.all["internal"].style.visibility = "hidden";
			document.frmActivity.isInternal[0].checked = true;
			document.frmActivity.isInternal[1].checked = false;
			document.frmActivity.chkReport.checked = false;
		}
	}
	
	function checkReport() {
		if (document.frmActivity.isInternal[0].checked) {
			document.all["report"].style.visibility = "hidden";
			document.frmActivity.chkReport.checked = false;
		} else {
			document.all["report"].style.visibility = "visible";
		}
	}
	//-->
</script>

<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE" class="small">
	<?=drawHeaderRow("Edit Activity", 3);?>
	<form method="post" action="<?=$_josh["request"]["path_query"]?>" name="frmActivity">
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#EEEEEE"><nobr>Award:</nobr></td>
		<td colspan="2" width="99%"><b><a href="award_view.php?id=<?=$r["awardID"]?>"><?=$r["awardTitle"]?></a></b> (awarded by <b><a href="resources_funder_view.php?id=<?=$r["funderID"]?>"><?=$r["name"]?></a></b>)</td>
	</tr>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6"><nobr>Activity Title:</nobr></td>
		<td colspan="2" width="99%"><?=draw_form_text("activityTitle", $r["activityTitle"])?></b></td>
	</tr>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6"><nobr>Activity Date:</nobr></td>
		<td colspan="2" width="99%"><?=drawFormDate("activityDate", $r["activityDate"])?></td>
	</tr>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6"><nobr>Staff Responsible:</nobr></td>
		<td colspan="2" width="99%">
			<?=drawSelectUser("activityAssignedTo", $r["activityAssignedTo"]);?>
		</td>
	</tr>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6" valign="top"><nobr>Type:</nobr></td>
		<td colspan="2" width="99%">
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
				<tr>
					<td width="16"><input type="checkbox" name="chkActionItem" onclick="javascript:checkInternal(this);"<?if($r["isActionItem"]) {?> checked<?}?>></td>
					<td width="99%">Is this an Action Item?</td>
				</tr>
			</table>
			<div id="internal"<?if(!$r["isActionItem"]) {?> style="visibility:hidden;"<?}?>>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
				<tr>
					<td width="16"><input type="radio" name="isInternal" value="true" onclick="javascript:checkReport();"<?if($r["isInternalDeadline"]) {?> checked<?}?>></td>
					<td width="99%">Internal Deadline</td>
				</tr>
				<tr>
					<td width="16"><input type="radio" name="isInternal" value="false" onclick="javascript:checkReport();"<?if(!$r["isInternalDeadline"]) {?> checked<?}?>></td>
					<td width="99%">External Deadline</td>
				</tr>
			</table>
			</div>
			<div id="report" <?if((!$r["isActionItem"]) || ($r["isInternalDeadline"])) {?>style="visibility:hidden;"<?}?>>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
				<tr>
					<td width="16"><?=draw_form_checkbox("chkReport", $r["isReport"]);?></td>
					<td width="99%">Is this a Report?</td>
				</tr>
			</table>
			</div>
		</td>
	</tr>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6"><nobr>Status:</nobr></td>
		<td colspan="2" width="99%">
			<select class="field" name="isComplete">
				<option value="0" <?if(!$r["isComplete"]){?> selected<?}?>>Incomplete</option>
				<option value="1" <?if( $r["isComplete"]){?> selected<?}?>>Complete</option>
			</select>
		</td>
	</tr>
	<tr class="helptext" bgcolor="#FFFFFF">
		<td bgcolor="#F6F6F6" valign="top"><nobr>Notes:</nobr></td>
		<td colspan="2" width="99%" valign="top"><?=draw_form_textarea("activityText",$r["activityText"])?></td>
	</tr>
	<tr bgcolor="#F6F6F6">
		<td colspan="3" align="center"><?=draw_form_submit("save changes")?></td>
	</tr>
	</form>
</table>
<? drawBottom(); ?>