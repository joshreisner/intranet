<?	include("include.php");

drawTop();
echo drawJumpToStaff();

?>

<table class="left" cellspacing="1">
	<?=drawHeaderRow("Emergency Contact Information", 3);?>
	<tr>
		<th align="left" width="30%">Name</th>
		<th align="left" width="35%">Personal Info</th>
		<th align="left" width="35%">Emergency Contacts</th>
	</tr>
	<?
	$result = db_query("SELECT 
							u.userID,
							u.firstname first_name,
							u.lastname last_name,
							u.homeAddress1,
							u.homeAddress2,
							u.homeCity,
							s.stateAbbrev as homeState,
							u.homeZIP,
							u.homePhone,
							u.homeCell,
							u.homeEmail,
							u.emerCont1Name,
							u.emerCont1Relationship,
							u.emerCont1Phone,
							u.emerCont1Cell,
							u.emerCont1Email,
							u.emerCont2Name,
							u.emerCont2Relationship,
							u.emerCont2Phone,
							u.emerCont2Cell,
							u.emerCont2Email
						FROM intranet_users u
						LEFT  JOIN intranet_us_states s ON s.stateID = u.homeStateID
						WHERE u.isActive = 1
						ORDER BY u.lastname, u.firstname");
	while ($r = db_fetch($result)) {?>
		<tr>
			<td rowspan="2"><a href="/staff/view.php?id=<?=$r["userID"]?>"><?=$r["last_name"]?>, <?=$r["first_name"]?></a></td>
			<td rowspan="2">
				<?=$r["homeAddress1"]?><br>
				<?if ($r["homeAddress2"]) {?><?=$r["homeAddress2"]?><br><?}?>
				<?if ($r["homeCity"]) {?><?=$r["homeCity"]?>, <?=$r["homeState"]?> <?=$r["homeZIP"]?><br><?}?>
				<?if ($r["homePhone"]) {?><?=$r["homePhone"]?> (Home)<br><?}?>
				<?if ($r["homeCell"]) {?><?=$r["homeCell"]?> (Cell)<br><?}?>
				<a href="mailto:<?=$r["homeEmail"]?>"><?=$r["homeEmail"]?></a>
			</td>
			<td>
				<?if ($r["emerCont1Name"]) {?><?=$r["emerCont1Name"]?> (<?=$r["emerCont1Relationship"]?>)<br><?}?>
				<?if ($r["emerCont1Phone"]) {?><?=$r["emerCont1Phone"]?><?}?> 
				<?if ($r["emerCont1Phone"] && $r["emerCont1Cell"]) {?> / <?}?>
				<?if ($r["emerCont1Cell"]) {?><?=$r["emerCont1Cell"]?><?}?>
				<?if ($r["emerCont1Phone"] || $r["emerCont1Cell"]) {?><br><?}?>
				<a href="mailto:<?=$r["emerCont1Email"]?>"><?=$r["emerCont1Email"]?></a>
			</td>
		</tr>
		<tr>
			<td>
				<?if ($r["emerCont2Name"]) {?><?=$r["emerCont2Name"]?> (<?=$r["emerCont2Relationship"]?>)<br><?}?>
				<?if ($r["emerCont2Phone"]) {?><?=$r["emerCont2Phone"]?><?}?> 
				<?if ($r["emerCont2Phone"] && $r["emerCont2Cell"]) {?> / <?}?>
				<?if ($r["emerCont2Cell"]) {?><?=$r["emerCont2Cell"]?><?}?>
				<?if ($r["emerCont2Phone"] || $r["emerCont2Cell"]) {?><br><?}?>
				<a href="mailto:<?=$r["emerCont2Email"]?>"><?=$r["emerCont2Email"]?></a>
			</td>
		</tr>
	<? }?>
</table>
<? drawBottom();?>