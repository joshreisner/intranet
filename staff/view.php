<?php
include("include.php");

//delete user handled by include
if (url_action("undelete")) { //undelete user
	db_query("UPDATE users SET is_active = 1, deleted_user = NULL, deleted_date = NULL, endDate = NULL, updated_user = {$_SESSION['user_id']}, updated_date = GETDATE() WHERE id = " . $_GET['id']);
	url_query_drop("action");
} elseif (url_action("passwd")) {
	db_query("UPDATE users SET password = NULL WHERE id = " . $_GET['id']);
	if ($_GET['id'] == $_SESSION['user_id']) {
		//if is user, make em reset pw now
		$_SESSION['password'] = true;
	} else {
		//otherwise send email
		emailPassword($_GET['id']);
	}
	url_query_drop("action");
} elseif (url_action("invite")) {
	emailInvite($_GET['id']);
	url_query_drop("action");
}

url_query_require();

echo drawTop();

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
		u.officeID,
		d.departmentName,
		u.organization_id,
		o.title' . langExt() . ' organization,
		u.homeAddress1,
		u.homeAddress2,
		u.homeCity,
		s.stateAbbrev,
		u.homeZIP,
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
		r.description rank,
		l.title language,
		' . db_updated('u') . '
	FROM users u
	JOIN languages l ON u.language_id = l.id
	LEFT JOIN users_to_channels u2c ON u.id = u2c.user_id
	LEFT JOIN channels			c ON u2c.channel_id = c.id
	LEFT JOIN intranet_ranks	r ON u.rankID = r.id
	LEFT JOIN organizations		o ON u.organization_id = o.id
	LEFT JOIN departments		d ON d.departmentID	= u.departmentID 				
	LEFT JOIN offices    		f ON f.id			= u.officeID 				
	LEFT JOIN intranet_us_states		s ON u.homeStateID	= s.stateID
	WHERE u.id = ' . $_GET['id']);

$r['nickname'] = trim($r['nickname']);

$r['organization'] = (empty($r['organization'])) ? '<a href="organizations.php?id=0">' . getString('shared') . '</a>' : '<a href="organizations.php?id=' . $r['organization_id'] . '">' . $r['organization'] . '</a>';


//if (!isset($r['is_active'])) url_change("./");

if (!$img = draw_img(file_dynamic('users', 'image_large', $_GET['id'], 'jpg', $r['updated']))) $img = draw_img(DIRECTORY_WRITE . "/images/to-be-taken.png");
file_dynamic('users', 'image_medium', $_GET['id'], 'jpg', $r['updated']);
file_dynamic('users', 'image_small', $_GET['id'], 'jpg', $r['updated']);

echo drawJumpToStaff($_GET['id']);

if (!$r['is_active']) {
	$msg = "This is a former staff member.  ";
	if ($r['endDate']) {
		$msg .= ($r['nickname']) ? $r['nickname'] : $r['firstname'];
		$msg .= "'s last day was " . format_date($r['endDate']) . ".";
	}
	echo drawMessage($msg, "center");
}
?>
<table class="left" cellspacing="1">
	<?php
	if ($page['is_admin']) {
		if ($r['is_active']) {
			echo drawHeaderRow($page['breadcrumbs'] . $page['title'], 3, getString('edit'), "add_edit.php?id=" . $_GET['id'], getString('delete'), drawDeleteLink("Deactivate this staff member?"));
		} else {
			echo drawHeaderRow($page['breadcrumbs'] . $page['title'], 3, getString('edit'), "add_edit.php?id=" . $_GET['id'], "re-activate", drawDeleteLink("Re-activate this staff member?", false, "undelete"));
		}
	} elseif ($_GET['id'] == $_SESSION['user_id']) {
		echo drawHeaderRow($page['title'], 3, getString('edit'), "add_edit.php?id=" . $_GET['id']);
	} else {
		echo drawHeaderRow($page['title'], 3);
	}
	$rowspan = 6;
	if (getOption("staff_showdept")) $rowspan++;
	if (getOption("staff_showoffice")) $rowspan++;
	if (getOption("languages")) $rowspan++;
	?>
	<tr>
		<td class="left"><?php echo getString('name')?></td>
		<td class="title"><?php echo $r['firstname']?> <?php if (!empty($r['nickname'])) {?>(<?php echo $r['nickname']?>) <?php }?><?php echo $r['lastname']?></td>
		<td rowspan="<?php echo $rowspan?>" style="width:240px; text-align:center; vertical-align:middle; padding:0px;"><?php echo $img?></td>
	</tr>
	<tr>
		<td class="left"><?php echo getString('organization')?></td>
		<td><?php echo $r['organization']?></td>
	</tr>
	<tr>
		<td class="left"><?php echo getString('staff_title')?></td>
		<td><?php echo $r['title']?></td>
	</tr>
	<?php if (getOption("staff_showdept")) {?>
	<tr>
		<td class="left"><?php echo getString('department')?></td>
		<td><?php echo $r['departmentName']?></td>
	</tr>
	<?php }
	if (getOption("staff_showoffice")) {?>
	<tr>
		<td class="left"><?php echo getString('location')?></td>
		<td><?php echo $r['office']?></td>
	</tr>
	<?php }
	if (getOption("languages")) {?>
	<tr>
		<td class="left"><?php echo getString('language')?></td>
		<td><?php echo $r['language']?></td>
	</tr>
	<?php }?>
	<tr>
		<td class="left"><?php echo getString('telephone')?></td>
		<td><?php echo format_phone($r['phone'])?></td>
	</tr>
	<tr>
		<td class="left"><?php echo getString('email')?></td>
		<td><a href="mailto:<?php echo $r['email']?>"><?php echo $r['email']?></a></td>
	</tr>
	<tr>
		<td class="left"><?php echo getString('last_login')?></td>
		<td><?php echo format_date_time($r['lastlogin'], " ")?></td>
	</tr>
	<tr>
		<td class="left"><?php echo getString('bio')?></td>
		<td colspan="2" height="167" class="text"><?php echo nl2br($r['bio'])?></td>
	</tr>
	<?php if ($page['is_admin'] || ($_GET['id'] == $_SESSION['user_id'])) {?>
	<tr class="group">
		<td colspan="3"><?php echo getString('administrative_info')?></td>
	</tr>
	<?php
	if (getOption("channels")) {?>
	<tr>
		<td class="left"><?php echo getString('network')?></td>
		<td colspan="2" class="bigger"><?php echo $r['channel']?></td>
	</tr>
	<?php }
	if (getOption('staff_ldcode') && ($r['officeID'] == 1)) {?>
	<tr>
		<td class="left">Telephone Code</td>
		<td colspan="2" class="bigger"><?php echo $r['longDistanceCode']?></td>
	</tr>
	<?php }
	if ($r['startDate']) {?>
	<tr>
		<td class="left"><?php echo getString('start_date')?></td>
		<td colspan="2"><?php echo format_date($r['startDate'])?></td>
	</tr>
	<?php }
	if ($r['endDate']) {?>
	<tr>
		<td class="left">End Date</td>
		<td colspan="2"><?php echo format_date($r['endDate'])?></td>
	</tr>
	<?php }
	if ($_GET['id'] == $_SESSION['user_id']) {
		?>
		<tr>
			<td class="left"><?php echo getString('password')?></td>
			<td colspan="2"><a href="<?php echo drawDeleteLink("Reset password?", $_GET['id'], "passwd")?>" class="button" style="line-height:13px;"><?php echo getString('password_reset')?></a></td>
		</tr>
		<?php } elseif ($page['is_admin']) {?>
		<tr>
			<td class="left"><?php echo getString('password')?></td>
			<td colspan="2">
				<?php if ($r['password']){?>
					<i><?php echo getString('password_is_reset')?></i>
				<?php } else {?>
					<a href="<?php echo drawDeleteLink(getString('are_you_sure'), $_GET['id'], "passwd")?>" class="button" style="line-height:13px;"><?php echo getString('password_reset')?></a>
				<?php }?>
			</td>
		</tr>
	<?php }?>
	<?php if ($page['is_admin']) {?>
		<tr>
			<td class="left"><?php echo getString('invite')?></td>
			<td colspan="2"><a href="<?php echo drawDeleteLink("Send email invite?", $_GET['id'], "invite")?>" class="button" style="line-height:13px;"><?php echo getString('invite_again')?></a></td>
		</tr>
		<?php if (getOption("staff_showrank")) {?>
			<tr>
				<td class="left">Rank</td>
				<td colspan="2"><?php echo $r['rank']?></td>
			</tr>
		<?php } ?>
		<tr>
			<td class="left"><?php echo getString('permissions')?></td>
			<td colspan="2">
			<?php
			if ($r['is_admin']) {
				echo "Site Administrator";
			} else {
				$permissions = array_merge(
					db_array('SELECT m.title' . langExt() . ' title FROM modules m JOIN users_to_modules a ON m.id = a.module_id WHERE a.user_id = ' . $_GET['id'] . ' AND a.is_admin = 1 ORDER BY m.title'),
					db_array('SELECT m.title' . langExt() . ' title FROM modulettes m JOIN users_to_modulettes a ON m.id = a.modulette_id WHERE a.user_id = ' . $_GET['id'] . ' ORDER BY m.title')
				);
				if (count($permissions)) {
					sort($permissions);
					echo draw_list($permissions);
				} else {
					echo getString('none');
				}
			}
			?>
			</td>
		</tr>
	<?php }
	
	if (getOption("staff_showhome")) {?>
	<tr class="group">
		<td colspan="3">Home Contact Information [private]</td>
	</tr>
	<tr>
		<td class="left">Home Address</nobr></td>
		<td colspan="2"><?php echo $r['homeAddress1']?><br>
			<?php if ($r['homeAddress2']) {?><?php echo $r['homeAddress2']?><br><?php }?>
			<?php echo $r['homeCity']?>, <?php echo $r['stateAbbrev']?> <?php echo $r['homeZIP']?>
		</td>
	</tr>
	<tr>
		<td class="left">Home Phone</nobr></td>
		<td colspan="2"><?php echo format_phone($r['homePhone'])?></td>
	</tr>
	<tr>
		<td class="left">Cell Phone</td>
		<td colspan="2"><?php echo format_phone($r['homeCell'])?></td>
	</tr>
	<tr>
		<td class="left">Personal Email</td>
		<td colspan="2"><a href="mailto:<?php echo $r['homeEmail']?>"><?php echo $r['homeEmail']?></a></td>
	</tr>
	<?php }
	if (getOption("staff_showemergency")) {?>
	<tr class="group">
		<td colspan="3">Emergency Contact Information [private]</td>
	</tr>
	<tr>
		<td class="left"><?php echo $r['emerCont1Relationship']?></td>
		<td colspan="2">
			<b><?php echo $r['emerCont1Name']?></b><br>
			<?php if($r['emerCont1Phone']) {?><?php echo format_phone($r['emerCont1Phone'])?><br><?php }?>
			<?php if($r['emerCont1Cell']) {?><?php echo format_phone($r['emerCont1Cell'])?><br><?php }?>
			<?php echo $r['emerCont1Email']?>
		</td>
	</tr>
	<tr>
		<td class="left"><?php echo $r['emerCont2Relationship']?></td>
		<td colspan="2">
			<b><?php echo $r['emerCont2Name']?></b><br>
			<?php if($r['emerCont2Phone']) {?><?php echo format_phone($r['emerCont2Phone'])?><br><?php }?>
			<?php if($r['emerCont2Cell']) {?><?php echo format_phone($r['emerCont2Cell'])?><br><?php }?>
			<?php echo $r['emerCont2Email']?>
		</td>
	</tr>
	<?php }
	}?>
</table>
<?php echo drawBottom();?>