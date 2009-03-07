<?
include("include.php");

//delete user handled by include
if (url_action("undelete")) { //undelete user
	db_query("UPDATE users SET isActive = 1, deletedBy = NULL, deletedOn = NULL, endDate = NULL, updatedBy = {$_SESSION["user_id"]}, updatedOn = GETDATE() WHERE userID = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("passwd")) {
	db_query("UPDATE users SET password = PWDENCRYPT('') WHERE userID = " . $_GET["id"]);
	$r = db_grab("SELECT userID, email FROM users WHERE userID = " . $_GET["id"]);
	email_user($r["email"], "Intranet Password Reset", drawEmptyResult($_SESSION["firstname"] . ' has just reset your password on the Intranet.  To pick a new password, please <a href="http://' . $_josh["request"]["host"] . '/login/password_reset.php?id=' . $r["userID"] . '">follow this link</a>.'));
	url_query_drop("action");
} elseif (url_action("invite")) {
	$r = db_grab("SELECT nickname, email, firstname FROM users WHERE userID = " . $_GET["id"]);
	$name = (!$r["nickname"]) ? $r["firstname"] : $r["nickname"];
	email_invite($_GET["id"], $r["email"], $name);
	url_query_drop("action");
}

//url_query_require();

$r = db_grab("SELECT 
		u.firstname,
		u.lastname,
		u.nickname, 
		u.bio, 
		u.email,
		" . db_pwdcompare("", "u.password") . " password,
		u.phone, 
		u.lastlogin, 
		u.title,
		f.name office, 
		d.departmentName,
		u.corporationID,
		c.description corporationName,
		u.imageID,
		m.width,
		m.height,
		u.homeAddress1,
		u.homeAddress2,
		u.homeCity,
		s.stateAbbrev,
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
		u.emerCont2Email,
		u.startDate,
		u.longDistanceCode,
		u.endDate,
		u.isActive,
		r.description rank
	FROM users u
	LEFT JOIN intranet_ranks r ON u.rankID = r.id
	LEFT JOIN organizations			c ON u.corporationID = c.id
	LEFT JOIN departments		d ON d.departmentID	= u.departmentID 				
	LEFT JOIN intranet_offices    		f ON f.id			= u.officeID 				
	LEFT JOIN intranet_images     		m ON u.imageID		= m.imageID
	LEFT JOIN intranet_us_states		s ON u.homeStateID	= s.stateID
	WHERE u.userID = " . $_GET["id"]);
				
$r["corporationName"] = (empty($r["corporationName"])) ? '<a href="organizations.php?id=0">Shared</a>' : '<a href="organizations.php?id=' . $r["corporationID"] . '">' . $r["corporationName"] . '</a>';

if (!isset($r["isActive"])) url_change("./");

drawTop();

//get image props
if (isset($r["imageID"]) && $r["imageID"]) {
	$img = "<img src=\"" . $locale . "staff/" . $r["imageID"] . ".jpg\" width=\"" . $r["width"] . "\" height=\"" . $r["height"] . "\" border=\"0\">";
	verifyImage($r["imageID"]);
} else {
	$img = "<img src='" . $locale . "images/to-be-taken.png' width='240' height='167' border='0'>";
}

echo drawJumpToStaff($_GET["id"]);

if (!$r["isActive"]) {
	$msg = "This is a former staff member.  ";
	if ($r["endDate"]) {
		$msg .= ($r["nickname"]) ? $r["nickname"] : $r["firstname"];
		$msg .= "'s last day was " . format_date($r["endDate"]) . ".";
	}
	echo drawServerMessage($msg, "center");
}
?>
<table class="left" cellspacing="1">
	<? if ($isAdmin) {
		if ($r["isActive"]) {
			echo drawHeaderRow("View Staff Info", 3, "edit", "add_edit.php?id=" . $_GET["id"], "deactivate", deleteLink("Deactivate this staff member?"));
		} else {
			echo drawHeaderRow("View Staff Info", 3, "edit", "add_edit.php?id=" . $_GET["id"], "re-activate", deleteLink("Re-activate this staff member?", false, "undelete"));
		}
	} elseif ($_GET["id"] == $_SESSION["user_id"]) {
		echo drawHeaderRow("View Staff Info", 3, "edit your info", "add_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow("View Staff Info", 3);
	} ?>
	<tr>
		<td class="left">Name</td>
		<td width="99%" class="big"><?=$r["firstname"]?> <? if ($r["nickname"]) {?>(<?=$r["nickname"]?>) <? }?><?=$r["lastname"]?></td>
		<td rowspan="8" style="width:271px; text-align:center; vertical-align:middle;"><?=$img?></td>
	</tr>
	<tr>
		<td class="left">Organization</td>
		<td><?=$r["corporationName"]?></td>
	</tr>
	<tr>
		<td class="left">Title</td>
		<td><?=$r["title"]?></td>
	</tr>
	<tr>
		<td class="left">Department</td>
		<td><?=$r["departmentName"]?></td>
	</tr>
	<tr>
		<td class="left">Office</td>
		<td><?=$r["office"]?></td>
	</tr>
	<tr>
		<td class="left">Phone</td>
		<td><?=format_phone($r["phone"])?></td>
	</tr>
	<tr>
		<td class="left">Email</td>
		<td><a href="mailto:<?=$r["email"]?>"><?=$r["email"]?></a></td>
	</tr>
	<tr>
		<td class="left">Last Login</td>
		<td><?=format_date_time($r["lastlogin"], " ")?></td>
	</tr>
	<tr>
		<td class="left">Bio</td>
		<td colspan="2" height="167" class="text"><?=nl2br($r["bio"])?></td>
	</tr>
	<? if ($isAdmin || ($_GET["id"] == $_SESSION["user_id"])) {?>
	<tr class="group">
		<td colspan="3">Intranet</td>
	</tr>
	<? if ($r["longDistanceCode"]) {?>
	<tr>
		<td class="left">Telephone Code</td>
		<td colspan="2" class="bigger"><?=$r["longDistanceCode"]?></td>
	</tr>
	<? }
	if ($r["startDate"]) {?>
	<tr>
		<td class="left">Start Date</td>
		<td colspan="2"><?=format_date($r["startDate"])?></td>
	</tr>
	<? }
	if ($r["endDate"]) {?>
	<tr>
		<td class="left">End Date</td>
		<td colspan="2"><?=format_date($r["endDate"])?></td>
	</tr>
	<? }
	if ($_GET["id"] == $_SESSION["user_id"]) {
		?>
		<tr>
			<td class="left">Password</td>
			<td colspan="2"><a href="<?=deleteLink("Reset password?", $_GET["id"], "passwd")?>" class="button" style="line-height:13px;">change your password</a></td>
		</tr>
		<? } elseif ($isAdmin) {?>
		<tr>
			<td class="left">Password</td>
			<td colspan="2">
				<? if ($r["password"]){?>
					<i>password is reset</i>
				<? } else {?>
					<a href="<?=deleteLink("Reset password?", $_GET["id"], "passwd")?>" class="button" style="line-height:13px;">reset password</a>
				<? }?>
			</td>
		</tr>
	<? }?>
	<? if ($isAdmin) {?>
	<tr>
		<td class="left">Invite</td>
		<td colspan="2"><a href="<?=deleteLink("Send email invite?", $_GET["id"], "invite")?>" class="button" style="line-height:13px;">re-invite user</a></td>
	</tr>
	<tr>
		<td class="left">Rank</td>
		<td colspan="2"><?=$r["rank"]?></td>
	</tr>
	<tr>
		<td class="left">Permissions</td>
		<td colspan="2">
		<?
		$hasPermission = false;
		$permissions = db_query("SELECT 
			m.name,
			m.isPublic,
			p.url
			FROM modules m 
			JOIN pages p ON m.homePageID = p.id
			JOIN users_to_modules a ON m.id = a.moduleID
			WHERE a.userID = {$_GET["id"]}
			ORDER BY m.name");
		while ($p = db_fetch($permissions)) {
			$hasPermission = true;
			echo "&#183;&nbsp;";
			if ($p["isPublic"]) echo "<a href='" . $p["url"] . "'>";
			echo $p["name"];
			if ($p["isPublic"]) echo "</a>";
			echo "<br>";
		}
		if (!$hasPermission) echo "None";
		?>
			
		</td>
	</tr>
	<? }?>
	<tr class="group">
		<td colspan="3">Home Contact Information [private]</td>
	</tr>
	<tr>
		<td class="left">Home Address</nobr></td>
		<td colspan="2"><?=$r["homeAddress1"]?><br>
			<? if ($r["homeAddress2"]) {?><?=$r["homeAddress2"]?><br><? }?>
			<?=$r["homeCity"]?>, <?=$r["stateAbbrev"]?> <?=$r["homeZIP"]?>
		</td>
	</tr>
	<tr>
		<td class="left">Home Phone</nobr></td>
		<td colspan="2"><?=format_phone($r["homePhone"])?></td>
	</tr>
	<tr>
		<td class="left">Cell Phone</td>
		<td colspan="2"><?=format_phone($r["homeCell"])?></td>
	</tr>
	<tr>
		<td class="left">Personal Email</td>
		<td colspan="2"><a href="mailto:<?=$r["homeEmail"]?>"><?=$r["homeEmail"]?></a></td>
	</tr>
	<tr class="group">
		<td colspan="3">Emergency Contact Information [private]</td>
	</tr>
	<tr>
		<td class="left"><?=$r["emerCont1Relationship"]?></td>
		<td colspan="2">
			<b><?=$r["emerCont1Name"]?></b><br>
			<? if($r["emerCont1Phone"]) {?><?=format_phone($r["emerCont1Phone"])?><br><? }?>
			<? if($r["emerCont1Cell"]) {?><?=format_phone($r["emerCont1Cell"])?><br><? }?>
			<?=$r["emerCont1Email"]?>
		</td>
	</tr>
	<tr>
		<td class="left"><?=$r["emerCont2Relationship"]?></td>
		<td colspan="2">
			<b><?=$r["emerCont2Name"]?></b><br>
			<? if($r["emerCont2Phone"]) {?><?=format_phone($r["emerCont2Phone"])?><br><? }?>
			<? if($r["emerCont2Cell"]) {?><?=format_phone($r["emerCont2Cell"])?><br><? }?>
			<?=$r["emerCont2Email"]?>
		</td>
	</tr>
	<? }?>
</table>
<? drawBottom();?>