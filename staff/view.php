<?
include("include.php");

//delete user handled by include
if (url_action("undelete")) { //undelete user
	db_query("UPDATE users SET is_active = 1, deleted_user = NULL, deleted_date = NULL, endDate = NULL, updated_user = {$_SESSION["user_id"]}, updated_date = GETDATE() WHERE id = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("passwd")) {
	db_query("UPDATE users SET password = NULL WHERE id = " . $_GET["id"]);
	if ($_GET["id"] == $_SESSION["user_id"]) {
		//if is user, make em reset pw now
		$_SESSION["password"] = true;
	} else {
		//otherwise send email
		$r = db_grab("SELECT id, email FROM users WHERE id = " . $_GET["id"]);
		$message = str_replace('%LINK%', url_base() . '/login/password_reset.php?id=' . $r["id"], getString('email_password_message'));
		emailUser($r["email"], getString('email_password_subject'), drawEmptyResult($message));
	}
	url_query_drop("action");
} elseif (url_action("invite")) {
	$r = db_grab("SELECT nickname, email, firstname FROM users WHERE id = " . $_GET["id"]);
	$name = (!$r["nickname"]) ? $r["firstname"] : $r["nickname"];
	emailInvite($_GET["id"], $r["email"], $name);
	url_query_drop("action");
}

url_query_require();

$r = db_grab('SELECT 
		u.firstname,
		u.lastname,
		u.nickname, 
		u.bio' . langExt() . ' bio, 
		u.email,
		' . db_pwdcompare("", "u.password") . ' password,
		u.phone, 
		u.lastlogin, 
		u.title' . langExt() . ' title,
		f.name office, 
		d.departmentName,
		u.organization_id,
		o.title' . langExt() . ' organization,
		u.homeAddress1,
		u.homeAddress2,
		u.homeCity,
		s.stateAbbrev,
		u.homeZIP,
		u.notify_topics,
		c.title' . langExt() . ' channel,
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
		u.is_active,
		u.is_admin,
		r.description rank
	FROM users u
	LEFT JOIN users_to_channels u2c ON u.id = u2c.user_id
	LEFT JOIN channels			c ON u2c.channel_id = c.id
	LEFT JOIN intranet_ranks	r ON u.rankID = r.id
	LEFT JOIN organizations		o ON u.organization_id = o.id
	LEFT JOIN departments		d ON d.departmentID	= u.departmentID 				
	LEFT JOIN offices    		f ON f.id			= u.officeID 				
	LEFT JOIN intranet_us_states		s ON u.homeStateID	= s.stateID
	WHERE u.id = ' . $_GET["id"]);
	
$r["nickname"] = trim($r["nickname"]);

$r["organization"] = (empty($r["organization"])) ? '<a href="organizations.php?id=0">' . getString('shared') . '</a>' : '<a href="organizations.php?id=' . $r["organization_id"] . '">' . $r["organization"] . '</a>';

if (!isset($r["is_active"])) url_change("./");

echo drawTop();

if (!$img = draw_img(DIRECTORY_WRITE . "/dynamic/users-image_large-" . $_GET["id"] . ".jpg")) $img = draw_img(DIRECTORY_WRITE . "/images/to-be-taken.png");

echo drawJumpToStaff($_GET["id"]);

if (!$r["is_active"]) {
	$msg = "This is a former staff member.  ";
	if ($r["endDate"]) {
		$msg .= ($r["nickname"]) ? $r["nickname"] : $r["firstname"];
		$msg .= "'s last day was " . format_date($r["endDate"]) . ".";
	}
	echo drawMessage($msg, "center");
}
?>
<table class="left" cellspacing="1">
	<? if ($page['is_admin']) {
		if ($r["is_active"]) {
			echo drawHeaderRow($page['breadcrumbs'] . $page['title'], 3, getString('edit'), "add_edit.php?id=" . $_GET["id"], getString('delete'), drawDeleteLink("Deactivate this staff member?"));
		} else {
			echo drawHeaderRow($page['breadcrumbs'] . $page['title'], 3, getString('edit'), "add_edit.php?id=" . $_GET["id"], "re-activate", drawDeleteLink("Re-activate this staff member?", false, "undelete"));
		}
	} elseif ($_GET["id"] == $_SESSION["user_id"]) {
		echo drawHeaderRow($page['title'], 3, getString('edit'), "add_edit.php?id=" . $_GET["id"]);
	} else {
		echo drawHeaderRow($page['title'], 3);
	}
	$rowspan = 6;
	if (getOption("staff_showdept")) $rowspan++;
	if (getOption("staff_showoffice")) $rowspan++;
	?>
	<tr>
		<td class="left"><?=getString('name')?></td>
		<td class="title"><?=$r["firstname"]?> <? if (!empty($r["nickname"])) {?>(<?=$r["nickname"]?>) <? }?><?=$r["lastname"]?></td>
		<td rowspan="<?=$rowspan?>" style="width:267px; text-align:center; vertical-align:middle;"><?=$img?></td>
	</tr>
	<tr>
		<td class="left"><?=getString('organization')?></td>
		<td><?=$r["organization"]?></td>
	</tr>
	<tr>
		<td class="left"><?=getString('staff_title')?></td>
		<td><?=$r["title"]?></td>
	</tr>
	<? if (getOption("staff_showoffice")) {?>
	<tr>
		<td class="left"><?=getString('department')?></td>
		<td><?=$r["departmentName"]?></td>
	</tr>
	<? }
	if (getOption("staff_showoffice")) {?>
	<tr>
		<td class="left"><?=getString('office')?></td>
		<td><?=$r["office"]?></td>
	</tr>
	<? }?>
	<tr>
		<td class="left"><?=getString('telephone')?></td>
		<td><?=format_phone($r["phone"])?></td>
	</tr>
	<tr>
		<td class="left"><?=getString('email')?></td>
		<td><a href="mailto:<?=$r["email"]?>"><?=$r["email"]?></a></td>
	</tr>
	<tr>
		<td class="left"><?=getString('last_login')?></td>
		<td><?=format_date_time($r["lastlogin"], " ")?></td>
	</tr>
	<tr>
		<td class="left"><?=getString('bio')?></td>
		<td colspan="2" height="167" class="text"><?=nl2br($r["bio"])?></td>
	</tr>
	<? if ($page['is_admin'] || ($_GET["id"] == $_SESSION["user_id"])) {?>
	<tr class="group">
		<td colspan="3"><?=getString('administrative_info')?></td>
	</tr>
	
	<? if (getOption("bb_notifypost")) {?>
	<tr>
		<td class="left">Notify Posts</td>
		<td colspan="2" class="bigger"><?=format_boolean($r["notify_topics"])?></td>
	</tr>
	<? }
	if (getOption("channels")) {?>
	<tr>
		<td class="left"><?=getString('network')?></td>
		<td colspan="2" class="bigger"><?=$r["channel"]?></td>
	</tr>
	<? }
	if ($r["longDistanceCode"]) {?>
	<tr>
		<td class="left">Telephone Code</td>
		<td colspan="2" class="bigger"><?=$r["longDistanceCode"]?></td>
	</tr>
	<? }
	if ($r["startDate"]) {?>
	<tr>
		<td class="left"><?=getString('start_date')?></td>
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
			<td class="left"><?=getString('password')?></td>
			<td colspan="2"><a href="<?=drawDeleteLink("Reset password?", $_GET["id"], "passwd")?>" class="button" style="line-height:13px;"><?=getString('password_reset')?></a></td>
		</tr>
		<? } elseif ($page['is_admin']) {?>
		<tr>
			<td class="left"><?=getString('password')?></td>
			<td colspan="2">
				<? if ($r["password"]){?>
					<i><?=getString('password_is_reset')?></i>
				<? } else {?>
					<a href="<?=drawDeleteLink(getString('are_you_sure'), $_GET["id"], "passwd")?>" class="button" style="line-height:13px;"><?=getString('password_reset')?></a>
				<? }?>
			</td>
		</tr>
	<? }?>
	<? if ($page['is_admin']) {?>
		<tr>
			<td class="left"><?=getString('invite')?></td>
			<td colspan="2"><a href="<?=drawDeleteLink("Send email invite?", $_GET["id"], "invite")?>" class="button" style="line-height:13px;"><?=getString('invite_again')?></a></td>
		</tr>
		<? if (getOption("staff_showrank")) {?>
			<tr>
				<td class="left">Rank</td>
				<td colspan="2"><?=$r["rank"]?></td>
			</tr>
		<? } ?>
		<tr>
			<td class="left"><?=getString('permissions')?></td>
			<td colspan="2">
			<?
			if ($r["is_admin"]) {
				echo "Site Administrator";
			} else {
				$hasPermission = false;
				if ($permissions = db_array('SELECT m.title' . langExt() . ' title FROM modules m JOIN users_to_modules a ON m.id = a.module_id WHERE a.user_id = ' . $_GET["id"] . ' ORDER BY m.title')) {
					echo draw_list($permissions);
				} else {
					echo getString('none');
				}
			}
			?>
			</td>
		</tr>
	<? }
	
	if (getOption("staff_showhome")) {?>
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
	<? }
	if (getOption("staff_showemergency")) {?>
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
	<? }
	}?>
</table>
<?=drawBottom();?>