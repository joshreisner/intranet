<?  include("../include.php");

url_query_require();

if (isset($_GET["delActivity"])) { //delete activity note
	db_query("DELETE FROM resources_activity WHERE activityID = " . $_GET["delActivity"]);
	url_change("award_view.php?id=" . $_GET["id"]);
}

if (!empty($_POST)) {
	//add activity
	$date         = "'" . format_date_sql($_POST["cboDateMonth"], $_POST["cboDateDay"], $_POST["cboDateYear"]) . "'";
	$isActionItem = (isset($_POST["chkActionItem"])) ? 1 : 0;
	$isReport     = (isset($_POST["chkReport"]))     ? 1 : 0;
	$isInternal   = ($_POST["isInternal"] == "true") ? 1 : 0;
	$isComplete   = (!$isActionItem)                 ? 1 : 0;
		
	db_query("INSERT INTO resources_activity (
		awardID,
		activityTitle,
		activityText,
		activityDate,
		activityAssignedTo,
		isActionItem,
		isComplete,
		isReport,
		isInternalDeadline,
		activityPostedOn,
		activityPostedBy
	) VALUES (
		" . $_GET["id"] . ",
		'" . $_POST["txtTitle"] . "',
		'" . $_POST["tarDescription"] . "',
		$date,
		" . $_POST["cboStaff"] . ",
		$isActionItem,
		$isComplete,
		$isReport,
		$isInternal,
		GETDATE(),
		" . $_SESSION["user_id"] . ")");
	url_change();
}

drawTop();


	
$r = db_grab("SELECT 
		a.awardTitle,
		a.awardFilingNumber,
		f.funderID,
		f.name,
		a.awardStartDate,
		a.awardEndDate,
		at.awardTypeDesc,
		a.awardAmount,
		a.awardNotes,
		p.programDesc,
		p2.programDesc as programDesc2,
		ras.awardStatusDesc,
		ISNULL(u.nickname, u.firstname) + ' ' + u.lastname staffname
	FROM resources_awards a
	INNER JOIN resources_funders f ON a.funderID = f.funderID
	INNER JOIN resources_awards_statuses ras ON a.awardStatusID = ras.awardStatusID
	INNER JOIN intranet_programs p  ON a.awardprogramID  = p.programID
	LEFT  JOIN intranet_programs p2 ON a.awardprogramID2 = p2.programID
	INNER JOIN resources_awards_types at ON at.awardtype_id = a. awardtype_id
	INNER JOIN users u ON a.staffID = u.id
	WHERE awardID = " . $_GET["id"]);

	?>
<script language="javascript">
	<!--
	function validate(form) {
		if (form.txtTitle.value.length) return true;
		alert("Please enter a short description before proceeding");
		return false;
	}
		
	function deleteActivity(id,title) {
		if (confirm("Are you sure you want to delete this activity note?")) location.href="<?=$_josh["request"]["path_query"]?>&delActivity=" + id;
	}
	
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

<table class="left" cellspacing="1">
	<?
	if ($module_admin) {
		echo drawHeaderRow("View Award", 2, "edit", "award_add_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Award", 2);
	}?>
	<tr>
		<td class="left">Award Name</td>
		<td><b><?=$r["awardTitle"]?></b></td>
	</tr>
	<tr>
		<td class="left">Filing Number</td>
		<td><?=$r["awardFilingNumber"]?></td>
	</tr>
	<tr>
		<td class="left">Funder</td>
		<td><a href="funder_view.php?id=<?=$r["funderID"]?>"><?=$r["name"]?></a></td>
	</tr>
	<tr>
		<td class="left">Term</td>
		<td><?=date("F Y", strToTime($r["awardStartDate"]))?> - <?=date("F Y", strToTime($r["awardEndDate"]))?></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><?=$r["awardTypeDesc"]?></td>
	</tr>
	<tr>
		<td class="left">Status</td>
		<td><?=$r["awardStatusDesc"]?></td>
	</tr>
	<tr>
		<td class="left">Program</td>
		<td><?=$r["programDesc"]?></td>
	</tr>
	<? if($r["programDesc2"]) {?>
	<tr>
		<td class="left">Cross-List</td>
		<td><?=$r["programDesc2"]?></td>
	</tr>
	<? }?>
	<tr>
		<td class="left"><div style="float:right;">$</div>Amount</td>
		<td><b><?=number_format($r["awardAmount"])?></b></td>
	</tr>
	<tr>
		<td class="left">Project Description</td>
		<td><?=nl2br($r["awardNotes"])?></td>
	</tr>
	<tr>
		<td class="left">Award Contact</td>
		<td><?=$r["staffname"]?></td>
	</tr>
</table>

<table class="left" cellspacing="1">
	<tr>
		<td class="head" colspan="4">Activity</td>
	</tr>
	<?
	$activity = db_query("SELECT
			a.activityID,
			a.activityTitle,
			a.activityDate,
			ISNULL(u.nickname, u.firstname) + ' ' + u.lastname staffname,
			a.activityText,
			a.isComplete,
			a.isReport,
			a.isActionItem,
			a.isInternalDeadline
		FROM Resources_Activity a
		INNER JOIN users u ON a.activityAssignedTo = u.id
		WHERE a.awardID = {$_GET["id"]}
		ORDER BY a.activityDate DESC");
	if (db_found($activity)) {?>
		<tr bgcolor="#F6F6F6">
			<td>Assigned To, Date</td>
			<td>Title, Description</td>
			<td>Status</td>
			<td></td>
		</tr>
		<? while ($a = db_fetch($activity)) {
			$date    = ($a["activityDate"]) ? format_date($a["activityDate"]) : "N/A";
			$bgcolor = (!$a["isInternalDeadline"] && !$a["isComplete"]) ? "FFEEEE" : "FFFFFF";
			if (!$a["isActionItem"]) {
				$status = "Activity Note";
			} else {
				$status  = ($a["isInternalDeadline"]) ? "Internal"   : "External";
				$status .= ($a["isReport"])           ? " report"    : " deadline";
				$status .= ($a["isComplete"])         ? ", complete" : ", incomplete";
			}
			?>
		<tr class="helptext" bgcolor="<?=$bgcolor?>">
			<td width="120"><b><nobr><?=$a["staffname"]?>&nbsp;&nbsp;</nobr></b><br><nobr><?=$date?></nobr>&nbsp;</td>
			<td width="99%"><b><a href="activity_view.php?id=<?=$a["activityID"]?>"><?=$a["activityTitle"]?></a></b><br><?=$a["activityText"]?></td>
			<td><?=$status?></td>
			<td width="16"><a href="javascript:deleteActivity(<?=$a["activityID"]?>,'<?=$a["activityID"]?>');"><img src="<?=$_josh["write_folder"]?>/images/icons/delete.gif" width="16" height="16" border="0"></a></td>
		</tr>
		<? }
	} else {
		echo drawEmptyResult("No actiivty has been entered.", 4);
	}?>
</table>

<table class="left" cellspacing="1">
	<form action="<?=$_josh["request"]["path_query"]?>" method="post" onsubmit="javascript: return validate(this);" name="frmActivity">
	<tr>
		<td class="head">Add Activity to this Award</td>
	</tr>
	<tr>
		<td><br>
			<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
				<tr>
					<td width="50%" height="50">
						ACTIVITY TITLE<br>
						<?=draw_form_text("txtTitle", "", 35);?>
					</td>
					<td width="50%">
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
							<tr>
								<td width="16"><input type="checkbox" name="chkActionItem" onclick="javascript:checkInternal(this);"></td>
								<td width="99%">Is this an Action Item?</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td width="50%" height="50">
						DATE<br>
						<?=draw_form_date("cboDate");?>
					</td>
					<td width="50%" valign="middle">
						<div id="internal" style="visibility:hidden;">
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
							<tr>
								<td width="16"><input type="radio" name="isInternal" value="true" checked onclick="javascript:checkReport();"></td>
								<td width="99%">Internal Deadline</td>
							</tr>
							<tr>
								<td width="16"><input type="radio" name="isInternal" value="false" onclick="javascript:checkReport();"></td>
								<td width="99%">External Deadline</td>
							</tr>
						</table></div>
					</td>
				</tr>
				<tr>
					<td width="50%" height="50">
						STAFF RESPONSIBLE<br>
						<?=drawSelectUser("cboStaff", $_SESSION["user_id"]);?>
					</td>
					<td width="50%">
						<div id="report" style="visibility:hidden;">
						<table width="100%" cellpadding="0" cellspacing="0" border="0" class="small">
							<tr>
								<td width="16"><?=draw_form_checkbox("chkReport", 0);?></td>
								<td width="99%">Is this a Report?</td>
							</tr>
						</table></div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						NOTES<br>
						<?=draw_form_textarea("tarDescription", "");?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="bottom"><?=draw_form_submit("add activity")?></td>
	</tr>
	</form>
</table>
<? drawBottom(); ?>