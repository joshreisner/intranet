<?  
include("../include.php");

//if no id, go to funders
url_query_require();

//delete an award
if (isset($_GET["delAward"])) { 
	db_query("DELETE FROM resources_awards WHERE awardID = " . $_GET["delAward"]);
	url_change("funder_view.php?id=" . $_GET["id"]);
}

//delete activity (temporary)
if (isset($_GET["delActivity"])) { 
	db_query("DELETE FROM resources_activity WHERE activityID = " . $_GET["delActivity"]);
	url_change("funder_view.php?id=" . $_GET["id"]);
}
	
drawTop();

$r = db_grab("SELECT
			f.funderTypeID,
			f.name,
			ft.funderTypeDesc, 
			fs.FunderStatusDesc,
			ISNULL(u.nickname, u.firstname) + ' ' + u.lastname staffname
		FROM resources_funders f
		INNER JOIN resources_funders_types    ft ON ft.FunderTypeID = f.funderTypeID
		INNER JOIN resources_funders_statuses fs ON fs.FunderStatusID = f.FunderStatusID
		INNER JOIN users             u  ON u.user_id = f.staffID
		WHERE funderID = " . $_GET["id"]);
		
?>
<script language="javascript">
	<!--
	function deleteAward(id,title) {
		if (confirm("Are you sure you want to delete this award?")) location.href="<?=$_josh["request"]["path_query"]?>&delAward=" + id;
	}
		
	function deleteActivity(id,title) {
		if (confirm("Are you sure you want to delete this activity note?")) location.href="<?=$_josh["request"]["path_query"]?>&delActivity=" + id;
	}
	//-->
</script>
<table class="left" cellspacing="1">
	<? if ($module_admin) {
		echo drawHeaderRow("View Funder", 2, "edit", "funder_add_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Funder", 2);
	}?>
	<tr>
		<td class="left"><nobr>Name:</nobr></td>
		<td><b><?=$r["name"]?></b></td>
	</tr>
	<tr>
		<td class="left"><nobr>Type:</nobr></td>
		<td><?=$r["funderTypeDesc"]?></td>
	</tr>
	<tr>
		<td class="left"><nobr>Status:</nobr></td>
		<td><b><?=$r["FunderStatusDesc"]?></b></td>
	</tr>
	<tr>
		<td class="left"><nobr>Funder Contact:</nobr></td>
		<td><?=$r["staffname"]?></td>
	</tr>
	<tr valign="top">
		<td class="left"><nobr>Program Interests:</nobr></td>
		<td>
			<? 
			$result_programs = db_query("SELECT 
				programDesc 
				FROM intranet_programs p
				INNER JOIN Resources_Funders_Program_Interests fp on p.programID = fp.programID
				WHERE fp.funderID = " . $_GET["id"]);
			while ($rp = db_fetch($result_programs)) {?>
			&#183; <?=$rp["programDesc"]?><br>
			<? }?>
		</td>
	</tr>
	<tr valign="top">
		<td class="left"><nobr>Geographic Interests:</nobr></td>
		<td>
			<? 
			$result_geographic_areas = db_query("SELECT 
				geographicAreaDesc 
				FROM funders_geographic_areas g
				INNER JOIN Resources_Funders_Geographic_Interests gp on g.geographicAreaID = gp.geographicAreaID
				WHERE gp.funderID = " . $_GET["id"]);
			while ($rg = db_fetch($result_geographic_areas)) {?>
			&#183; <?=$rg["geographicAreaDesc"]?><br>
			<?}?>
		</td>
	</tr>
</table>

<table class="left" cellspacing="1">
	<tr>
		<td class="left" colspan="6">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td class="bold">Awards, Proposals, Strategies, etc.</td>
					<td class="small" align="right">
						<? if ($module_admin) {?>[ <a href="award_add_edit.php?funderID=<?=$_GET["id"]?>" class="black">add award / proposal</a> ]<?}?></td>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?
$result_award_statuses = db_query("SELECT 
					s.awardStatusID, 
					s.awardStatusDescPlural,
					(SELECT count(*) FROM resources_awards a WHERE a.awardStatusID = s.awardStatusID AND a.funderID = " . $_GET["id"] . ") as awardCount
				FROM resources_awards_statuses s");
$total_awards = 0;
while ($rsa = db_fetch($result_award_statuses)) {
	$total_awards += $rsa["awardCount"];
	if (!$rsa["awardCount"]) continue;
	?>
	<tr class="helptext">
		<td colspan="6" bgcolor="#FFFFFF" height="40" valign="bottom"><b><?=$rsa["awardStatusDescPlural"]?></b></td>
	</tr>
	<tr class="left">
		<th>Award Name</th>
		<th>Program</th>
		<th>Type</th>
		<th>Report Due?</th>
		<th>Amount</th>
		<th></th>
	</tr>
<?
	$totalAwards = 0;
	$result_awards = db_query("SELECT 
			a.awardID,
			a.awardAmount,
			at.awardTypeDesc,
			a.awardTitle,
			p.programDesc,
			(SELECT TOP 1 c.activityDate FROM resources_activity c WHERE c.awardID = a.awardID AND c.isReport = 1 AND c.isComplete = 0 ORDER BY c.activityDate ASC) reportDate
		FROM resources_awards a
		INNER JOIN resources_awards_types at ON a.awardTypeID = at.awardTypeID
		INNER JOIN intranet_programs p on a.awardProgramID = p.programID
		WHERE a.funderID = " . $_GET["id"] . " 
		AND a.awardStatusID = " . $rsa["awardStatusID"] . "
		ORDER BY a.awardStartDate DESC");
	while ($ra = db_fetch($result_awards)) {
		$totalAwards += $ra["awardAmount"];
	?>
	<tr>
		<td><a href="award_view.php?id=<?=$ra["awardID"]?>"><?=$ra["awardTitle"]?></a></td>
		<td><nobr><?=$ra["programDesc"]?></nobr></td>
		<td><nobr><?=$ra["awardTypeDesc"]?></nobr></td>
		<td align="right"><?=format_date($ra["reportDate"], false, "", "")?></td>
		<td align="right">$<?=number_format($ra["awardAmount"])?></td>
		<td width="16"><a href="javascript:deleteAward(<?=$ra["awardID"]?>);"><img src="<?=$_josh["write_folder"]?>/images/icons/delete.gif" width="16" height="16" border="0"></a></td>
	</tr>
	<? }?>
	<tr class="total">
		<td colspan="4" align="right" width="99%">Total:&nbsp;</td>
		<td align="right" class="bold-w" width="20">$<?=number_format($totalAwards)?></td>
		<td width="16"></td>
	</tr>
<? }
if (!$total_awards) echo drawEmptyResult("No awards entered yet!", 6);
?>
</table>
<? drawBottom(); ?>