<?  include("../../include.php");

//default to active awards
if (!isset($_GET["statusID"])) url_change($_josh["request"]["path_query"] . "&statusID=1");
	
echo drawTop();

$r  = db_grab("SELECT awardStatusDescPlural FROM funders_awards_statuses WHERE awardStatusID = " . $_GET["statusID"]);
$r2 = db_grab("SELECT ISNULL(u.nickname, u.firstname) staffname 
					FROM users u
					WHERE u.id = " . $_GET["staffID"]);
	
?>
<table width="100%" cellpadding="3" cellspacing="1" border="0" bgcolor="#EEEEEE" class="small">
	<?=drawHeaderRow($r2["staffname"] . "'s " . $r["awardStatusDescPlural"], 5)?>
<?
$programs = db_query("SELECT p.programID, p.programDesc, (SELECT count(*) 
		FROM funders_awards a
		INNER JOIN funders f on a.funderID = f.funderID
		WHERE a.awardProgramID = p.programID AND a.awardStatusID  = " . $_GET["statusID"] . " AND a.staffID  = " . $_GET["staffID"] . ") as progAwardCount
		FROM funders_programs p ORDER BY programDesc");
while ($rp = db_fetch($programs)) {
	$lastfunderID = 0;
	$award_amt    = 0; 
	if ($rp["progAwardCount"] > 0) {
	?>
	<tr>
		<td colspan="5" bgcolor="#FFFFFF" height="40" valign="bottom">
			<table width="100%" cellpadding="0" cellspacing="0" border="0">
				<tr class="helptext">
					<td><b><?=$rp["programDesc"]?></b></td>
					<td align="right" class="small">[ <a href="program.php?id=<?=$rp["programID"]?>">view program</a> ]</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td><nobr>Funder</nobr></td>
		<td><nobr>Title</nobr></td>
		<td><nobr>Grant Period</nobr></td>
		<td><nobr>Next Step / Status</nobr></td>
		<td align="right"><nobr>Amt. Awarded</nobr></td>
	</tr>
		<?
		$result = db_query("SELECT 
			a.awardID,
			a.awardTitle,
			f.funderID,
			f.name,
			a.awardAmount,
			a.awardStartDate,
			a.awardEndDate
		FROM funders_awards a
		INNER JOIN funders f on a.funderID = f.funderID
		WHERE a.awardProgramID = " . $rp["programID"] . "
			AND   a.awardStatusID  = " . $_GET["statusID"] . " 
			AND   a.staffID  = " . $_GET["staffID"] . " 
		ORDER BY f.name");
	 	
		while ($r = db_fetch($result)) {
			$award_amt += $r["awardAmount"];

	?>
		<tr bgcolor="#FFFFFF" class="helptext" valign="top">
			<? if ($lastfunderID != $r["funderID"]) { ?>
			<td width="39%" rowspan="<?
			$result_rowcount = db_query("SELECT count(*) as 'rowcount' FROM funders_awards WHERE funderID = " . $r["funderID"] . " AND awardprogramID = " . $rp["programID"] . " AND awardStatusID  = " . $_GET["statusID"] . " AND staffID  = " . $_GET["staffID"]);
			$rr = db_fetch($result_rowcount);
			echo $rr["rowcount"];
			?>"><a href="funder_view.php?id=<?=$r["funderID"]?>"><?=$r["name"]?></a></td>
			<?}?>
			<td width="40%"><a href="award_view.php?id=<?=$r["awardID"]?>"><?=$r["awardTitle"]?></a></td>
			<td><nobr><?=date("n/y", strToTime($r["awardStartDate"]))?> - <?=date("n/y", strToTime($r["awardEndDate"]))?></nobr></td>
			<td width="40%"><?
				$result_notes = db_query("SELECT 
						a.activityDate, 
						a.activityTitle,
						ISNULL(u.nickname, u.firstname) first,
						u.lastname last,
						a.isComplete
					FROM funders_activity a
					INNER JOIN users u     ON a.activityAssignedTo = u.id
					WHERE awardID = " . $r["awardID"] . " AND 
					((" . db_datediff("GETDATE()", "a.activityDate") . " > -60 AND " . db_datediff("GETDATE()", "a.activityDate") . " < 60) OR
					(" . db_datediff("GETDATE()", "a.activityDate") . " < 60) AND isComplete = 0)
					ORDER BY a.activityDate");
				
			while($rn = db_fetch($result_notes)) {
				if (!$rn["isComplete"]) echo "<b>";
				echo "<li>" . $rn["activityTitle"] . " (" . $rn["first"] . " " . $rn["last"] . " - " . format_date($rn["activityDate"]) . ")" . "</li>";
				if (!$rn["isComplete"]) echo "</b>";
			}
			?>
			
			</td>
			<td align="right">$<?=number_format($r["awardAmount"])?></td>
		</tr>
	<?	$lastfunderID = $r["funderID"];
	}?>
		<tr class="helptext">
			<td colspan="4" align="right">Total: </td>
			<td bgcolor="#666666" align="right"><font color="#FFFFFF"><b>$<?=number_format($award_amt)?></b></font></td>
		</tr>
	<?
	}
	}?>
</table>
<br><br>
<?=drawBottom(); ?>