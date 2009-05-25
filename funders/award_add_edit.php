<?  include("../include.php");

if (!empty($_POST)) { 
	//format variables
	$awardStartDate = date("Y-m-d H:i:00",mktime(1,1,1, $_POST["cboStartMonth"], 1, $_POST["cboStartYear"]));
	$awardEndDate   = date("Y-m-d H:i:00",mktime(1,1,1, $_POST["cboEndMonth"],   1, $_POST["cboEndYear"]));
	$_POST["cboProgram2"] = (isset($_POST["noCrossList"])) ? "NULL" : $_POST["cboProgram2"];
	if (isset($_GET["funderID"])) { //adding
		//insert award
		db_query("INSERT into resources_awards (
			funderID,
			awardAmount,
			awardtype_id,
			awardStatusID,
			awardStartDate,
			awardEndDate,
			awardTitle,
			awardFilingNumber,
			awardNotes,
			awardPostedOn,
			awardPostedBy,
			awardProgramID,
			awardProgramID2,
			staffID
		) VALUES (
			" . $_GET["funderID"] . ",
			" . $_POST["txtAmount"] . ",
			" . $_POST["cboAwardType"] . ",
			" . $_POST["cboAwardStatus"] . ",
			'$awardStartDate',
			'$awardEndDate',
			'" . $_POST["txtAwardTitle"] . "',
			'" . $_POST["txtAwardFilingNumber"] . "',
			'" . $_POST["tarDescription"] . "',
			GETDATE(),
			" . $_SESSION["user_id"] . ",
			" . $_POST["cboProgram"] . ",
			" . $_POST["cboProgram2"] . ",
			" . $_POST["cboStaff"] . "
		);");
		
		//determine awardID for redirecting
		$_GET["id"] = db_grab("SELECT max(awardID) FROM resources_awards");
	} else { //editing
		db_query("UPDATE resources_awards SET
			funderID          = " . $_POST["cboFunder"] . ",
			awardAmount       = " . $_POST["txtAmount"] . ",
			awardtype_id       = " . $_POST["cboAwardType"] . ",
			awardStatusID     = " . $_POST["cboAwardStatus"] . ",
			awardStartDate    = '$awardStartDate',
			awardEndDate      = '$awardEndDate',
			awardTitle        = '" . $_POST["txtAwardTitle"] . "',
			awardFilingNumber = '" . $_POST["txtAwardFilingNumber"] . "',
			awardNotes        = '" . $_POST["tarDescription"] . "',
			awardPostedOn     = GETDATE(),
			awardPostedBy     = " . $_SESSION["user_id"] . ",
			awardProgramID    = " . $_POST["cboProgram"] . ",
			awardProgramID2   = " . $_POST["cboProgram2"] . ",
			staffID           = " . $_POST["cboStaff"] . "
		WHERE awardID         = " . $_GET["id"]);
	}
		
	//redirect to view award
	url_change("award_view.php?id=" . $_GET["id"]);
}
	
drawTop();

	
if (isset($_GET["funderID"])) { //adding
	$adding = true;
	$r = db_grab("SELECT 
			f.name,
			f.staffID 
		FROM resources_funders f 
		WHERE f.funderID = " . $_GET["funderID"]);
	$startMonth = $month;
	$endMonth   = $month;
	$startYear  = $year;
	$endYear    = $year + 1;
	$button = "add award";
} else { //editing
	$adding = false;
	$r = db_grab("SELECT 
					f.funderID,
					f.name,
					a.awardTitle,
					a.awardFilingNumber,
					a.awardStartDate,
					a.awardEndDate,
					a.awardtype_id,
					a.awardStatusID,
					a.awardProgramID,
					a.awardProgramID2,
					a.awardAmount,
					a.awardNotes,
					a.staffID
				FROM resources_awards a
				INNER JOIN resources_funders f on a.funderID = f.funderID
				WHERE a.awardID = " . $_GET["id"]);
	$startMonth = date("n", strToTime(@$r["awardStartDate"]));
	$endMonth   = date("n", strToTime(@$r["awardEndDate"]));
	$startYear  = date("Y", strToTime(@$r["awardStartDate"]));
	$endYear    = date("Y", strToTime(@$r["awardEndDate"]));
	$button = "save changes";
}
	
?>
<script language="javascript">
	<!--
	function validate(form) {
		var errors = new Array();
		if (!form.txtAwardTitle.value.length) errors[errors.length] = "the award title is missing";
		if (!form.txtAmount.value.length) {
			errors[errors.length] = "the amount is missing";
		} else if (!isFinite(form.txtAmount.value)) {
			errors[errors.length] = "the amount must have only numbers";
		}
		return showErrors(errors);
	}
	//-->
</script>

<table class="left" cellspacing="1">
	<form name="frmAward" method="post" action="<?=$_josh["request"]["path_query"]?>" onsubmit="javascript:return validate(this);">
	<? if ($adding) {
		echo drawHeaderRow("Add an Award", 2);
	} else {
		echo drawHeaderRow("Edit Award", 2);
	}?>
	<tr>
		<td class="left">Award Name</td>
		<td><input type="text" value="<?=@$r["awardTitle"]?>" name="txtAwardTitle" maxlength="50" size="30" class="field"></td>
	</tr>
	<tr>
		<td class="left">Filing Number</td>
		<td><input type="text" value="<?=@$r["awardFilingNumber"]?>" name="txtAwardFilingNumber" maxlength="50" size="7" class="field"></td>
	</tr>
	<tr>
		<td class="left">Funder</td>
		<td>
			<? if (isset($_GET["funderID"])) {?>
				<a href="funder_view.php?id=<?=$_GET["funderID"]?>"><b><?=@$r["name"]?></b></a>
			<? } else {
				error_reporting(E_ALL);
				echo draw_form_select("cboFunder", "SELECT funderID, name from resources_funders order by name", @$r["funderID"], 60);
			}?>
		</td>
	</tr>
	<tr>
		<td class="left">Term Start Date</td>
		<td><nobr><select name="cboStartMonth" class="field">
			<? for ($i = 1; $i < 13; $i++) {
				$selected = ($i == $startMonth) ? " selected" : "";
					?>
			<option value="<?=$i?>"<?=$selected?>><?=$months[$i-1]?></option>
			<? }?>
		</select>
		<select name="cboStartYear" class="field">
			<? for ($i = 1990; $i < 2020; $i++) {
				$selected = ($i == $startYear) ? " selected" : "";
					?>
			<option value="<?=$i?>"<?=$selected?>><?=$i?></option>
			<?}?>
		</select></nobr></td>
	</tr>
	<tr>
		<td class="left">Term End Date</td>
		<td><nobr><select name="cboEndMonth" class="field">
			<? for ($i = 1; $i < 13; $i++) {
				$selected = ($i == $endMonth) ? " selected" : "";
				?>
			<option value="<?=$i?>"<?=$selected?>><?=$months[$i-1]?></option>
			<? }?>
		</select>
		<select name="cboEndYear" class="field">
			<? for ($i = 1990; $i < 2020; $i++) {
					$selected = ($i == $endYear) ? " selected" : "";
					?>
				<option value="<?=$i?>"<?=$selected?>><?=$i?></option>
				<? }?>
		</select></nobr></td>
	</tr>
	<tr>
		<td class="left">Type</td>
		<td><?=draw_form_select("cboAwardType","SELECT awardtype_id, awardTypeDesc FROM resources_awards_types ORDER BY awardTypeDesc", @$r["awardtype_id"]);?></td>
	</tr>
	<tr>
		<td class="left">Status</td>
		<td><?=draw_form_select("cboAwardStatus","SELECT awardStatusID, awardStatusDesc FROM resources_awards_statuses", @$r["awardStatusID"]);?></td>
	</tr>
	<tr>
		<td class="left">Program</td>
		<td><?=draw_form_select("cboProgram","SELECT programID, programDesc FROM intranet_programs ORDER BY programDesc",@$r["awardProgramID"]);?></td>
	</tr>
	<tr>
		<td class="left">Cross List</td>
		<td>
			<table cellpadding="0" cellspacing="0" border="0" class="small">
				<tr>
					<td><?=draw_form_select("cboProgram2","SELECT programID, programDesc FROM intranet_programs ORDER BY programDesc",@$r["awardProgramID2"]);?></td>
					<td>&nbsp;<?=draw_form_checkbox("noCrossList", @!@$r["awardProgramID2"]);?></td>
					<td>&nbsp;(no cross-listing)</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="left">Amount <div style="float:right">$</span></td>
		<td><input type="text" size="11" class="field" name="txtAmount" value="<?=@$r["awardAmount"]?>"></td>
	</tr>
	<tr>
		<td class="left">Project Description</td>
		<td><textarea name="tarDescription" cols="62" rows="8" class="field"><?=@$r["awardNotes"]?></textarea></td>
	</tr>
	<tr>
		<td class="left"><nobr>Award Contact:</nobr></td>
		<td width="99%">
			<?=drawSelectUser("cboStaff", @$r["staffID"]);?>
		</td>
	</tr>
	<tr>
		<td class="bottom" colspan="2"><?=draw_form_submit($button);?></td>
	</tr>
	</form>
</table>
<? drawBottom(); ?>