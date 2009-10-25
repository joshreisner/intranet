<?	include("../include.php");

if (url_action("delete")) {
	db_query("UPDATE contacts SET
				is_active = 0,
				deleted_date = GETDATE(),
				deleted_user = {$_SESSION["user_id"]}
			WHERE id = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("undelete")) {
	db_query("UPDATE contacts SET
				is_active = 1,
				deleted_date = NULL,
				deleted_user = NULL
			WHERE id = " . $_GET["id"]);
	url_query_drop("action");
} elseif (url_action("expunge")) {
	$result = db_query("SELECT id FROM contacts_instances WHERE objectID = " . $_GET["id"]);
	while ($r = db_fetch($result)) {
		db_query("delete from contacts_instances_to_tags where instanceID = " . $r["id"]);
		db_query("delete from contacts_instances_to_words where instanceID = " . $r["id"]);
		db_query("delete from contacts_instances where id = " . $r["id"]);
	}
	db_query("delete from contacts where id = " . $_GET["id"]);
	url_change("./");
}

url_query_require();
echo drawTop();


$i = db_grab("SELECT
		i.id,
		(SELECT t1.tag FROM contacts_tags t1 INNER JOIN contacts_instances_to_tags i2t1 ON t1.id = i2t1.tagID WHERE t1.is_active = 1 AND t1.type_id = 10 AND i2t1.instanceID = o.instanceCurrentID) salutation,
		i.varchar_01 first,
		i.varchar_02 last,
		(SELECT t2.tag FROM contacts_tags t2 INNER JOIN contacts_instances_to_tags i2t2 ON t2.id = i2t2.tagID WHERE t2.is_active = 1 AND t2.type_id = 11 AND i2t2.instanceID = o.instanceCurrentID) suffix,
		i.varchar_03 nickname,
		i.varchar_04 org,
		i.varchar_05 title,
		i.varchar_06 address1,
		i.varchar_07 address2,
		RIGHT('00000' + RTRIM(i.numeric_01), 5) zip,
		i.varchar_08 phone,
		i.varchar_09 fax,
		i.varchar_10 cell,
		i.varchar_11 email,
		o.is_active,
		o.deleted_date,
		o.deleted_user,
		ISNULL(u.nickname, u.firstname) + ' ' + u.lastname deleted_userName,
		z.city,
		z.state,
		i.text_01 notes
	FROM contacts o
	INNER JOIN contacts_instances i ON i.id = o.instanceCurrentID
	LEFT  JOIN zip_codes z ON i.numeric_01 = z.zip
	LEFT  JOIN users     u ON u.id = o.deleted_user
	WHERE o.id = " . $_GET["id"]);

if (!$i["id"]) {
	echo drawMessage("Either the link you clicked on is bad, or else this contact has been expunged from the system.  No further information is available.");
} else {
	if (!$i["is_active"]) echo drawMessage("This contact was deleted on " . format_date_excel($i["deleted_date"]) . " by <a href='/staff/view.php?id=" . $i["deleted_user"] . "'>" . $i["deleted_userName"] . "</a>.  You can click below to undo the deletion.");
	?>
	<script language="javascript">
		<!--
		function confirmDelete(id) {
			if (confirm("Are you sure you want to delete this contact?")) location.href='<?=url_action_add("delete")?>';
		}
		
		function confirmExpunge(id) {
			if (confirm("Are you sure you want to expunge this contact?")) location.href='<?=url_action_add("expunge")?>';
		}
		//-->
	</script>
	<table class="left" cellspacing="1">
		<?
		if ($page['is_admin'] && $i["is_active"]) {
			echo drawHeaderRow("View Contact", 3, "edit", "contact_edit.php?id=" . $_GET["id"], "delete", "javascript:confirmDelete({$_GET["id"]});");
		} elseif ($page['is_admin'] && !$i["is_active"]) {
			echo drawHeaderRow("View Contact", 3, "undelete", url_action_add("undelete"), "expunge", "javascript:confirmExpunge({$_GET["id"]});");
		} elseif ($i["is_active"]) {
			echo drawHeaderRow("View Contact", 3, "edit", "contact_edit.php?id=" . $_GET["id"], "delete", "javascript:confirmDelete({$_GET["id"]});");
		} else {
			echo drawHeaderRow("View Contact", 3, "undelete", url_action_add("undelete"));
		}?>
		<tr>
			<td class="left">Name</td>
			<td width="82%" colspan="2" class="input"><font size="+1"><b><? if(!$i["is_active"]) {?><strike><font color="#666666"><?}?><? if($i["salutation"]) {?><?=$i["salutation"]?> <?}?><?=$i["first"]?> <? if($i["nickname"]) {?>(<?=$i["nickname"]?>)<?}?> <?=$i["last"]?><? if($i["suffix"]) {?>, <?=$i["suffix"]?><?}?><? if(!$i["is_active"]) {?></strike></font><? }?></b></font></td>
		</tr>
		<? if ($i["org"]) {?>
		<tr>
			<td class="left">Company</td>
			<td colspan="2" class="input" width="82%"><?=$i["org"]?></td>
		</tr>
		<? }
		if ($i["title"]) {?>
		<tr>
			<td class="left">Job Title</td>
			<td colspan="2" class="input" width="82%"><?=$i["title"]?></td>
		</tr>
		<? }?>
		<tr valign="top">
			<td class="left">Address</td>
			<td colspan="2" class="input" width="82%"><?=$i["address1"]?><br><? if($i["address2"]) {?><?=$i["address2"]?><br><?}?><?=$i["city"]?>, <?=$i["state"]?> <?=$i["zip"]?></td>
		</tr>
		<? if ($i["phone"]) {?>
		<tr>
			<td class="left">Phone</td>
			<td colspan="2" class="input" width="82%"><?=$i["phone"]?></td>
		</tr>
		<? }
		if ($i["fax"]) {?>
		<tr>
			<td class="left">Fax</td>
			<td colspan="2" class="input" width="82%"><?=$i["fax"]?></td>
		</tr>
		<? }
		if ($i["cell"]) {?>
		<tr>
			<td class="left">Cell</td>
			<td colspan="2" class="input" width="82%"><?=$i["cell"]?></td>
		</tr>
		<? }
		if ($i["email"]) {?>
		<tr>
			<td class="left">E-mail Address</td>
			<td colspan="2" class="input" width="82%"><a href="mailto:<?=$i["email"]?>"><?=$i["email"]?></a></td>
		</tr>
		<? }
		if (strlen(trim($i["notes"]))) {?>
		<tr valign="top">
			<td class="left">Notes</td>
			<td colspan="2" class="input" width="82%"><?=nl2br($i["notes"])?></td>
		</tr>
		<? }
		$found = false;
		$output = '<tr class="group"><td colspan="3">Tags</td></tr>';
		$tags = db_query("SELECT 
						f.tagTypeID,
						f.name,
						f.fieldTypeID
					FROM contacts_fields f
					JOIN contacts_tags_types t ON f.tagTypeID = t.id
					WHERE f.objectTypeID = 22 AND f.tagTypeID > 11 AND t.is_active = 1 ORDER BY f.precedence");
		while ($t = db_fetch($tags)) {
			$values = db_query("SELECT t.id, t.tag FROM contacts_tags t JOIN contacts_instances_to_tags i2t ON t.id = i2t.tagID WHERE t.is_active = 1 AND t.type_id = {$t["tagTypeID"]} AND i2t.instanceID = {$i["id"]} ORDER BY t.precedence");
			if (db_found($values)) {
				$found = true;
				$output .= '<tr valign="top"><td class="left">' . $t["name"] . '</td>';
				$output .= '<td class="input" width="82%" colspan="2">';
				while ($v = db_fetch($values)) $output .= '&nbsp;&#187;&nbsp;<a href="value.php?id=' . $v["id"] . '">' . $v["tag"] . '</a><br>';
				$output .= '</td></tr>';
			}
		}
		if ($found) echo $output;
		$output = "";
		?>
		<tr class="group">
			<td colspan="3">Object History</td>
		</tr>
		<tr>
			<th width="18%" align="left">Who</th>
			<th width="60%" align="left">What</th>
			<th width="22%" align="right">When</th>
		</tr>
		<?
		$instances = db_query("SELECT
					i.id,
					o.instanceFirstID,
					o.instanceCurrentID,
					i.created_user,
					ISNULL(u.nickname, u.firstname) + ' ' + u.lastname createdName,
					i.created_date,
					i.varchar_01 firstname,
					i.varchar_02 lastname,
					i.varchar_03 nickname,
					i.varchar_04 organization,
					i.varchar_05 title,
					i.varchar_06 address1,
					i.varchar_07 address2,
					i.numeric_01 zip,
					i.varchar_08 phone,
					i.varchar_09 fax,
					i.varchar_10 cell,
					i.varchar_11 email,
					i.text_01 notes
				FROM contacts_instances i
				JOIN contacts   o ON i.objectID = o.id
				JOIN users     u ON i.created_user = u.id
				WHERE o.id = {$_GET["id"]}
				ORDER BY i.created_date ASC");
		while ($j = db_fetch($instances)) {
			if ($j["id"] == $j["instanceFirstID"]) {
				$description = "contact created";
				extract($j);
			} else {
				$description = "";
				$changes = array();
				if ($firstname    != $j["firstname"])    $changes[] = "firstname";
				if ($lastname     != $j["lastname"])     $changes[] = "lastname";
				if ($nickname     != $j["nickname"])     $changes[] = "nickname";
				if ($organization != $j["organization"]) $changes[] = "organization";
				if ($title        != $j["title"])        $changes[] = "title";
				if ($address1     != $j["address1"])     $changes[] = "address1";
				if ($address2     != $j["address2"])     $changes[] = "address2";
				if ($zip          != $j["zip"])          $changes[] = "zip";
				if ($phone        != $j["phone"])        $changes[] = "phone";
				if ($fax          != $j["fax"])          $changes[] = "fax";
				if ($cell         != $j["cell"])         $changes[] = "cell";
				if ($email        != $j["email"])        $changes[] = "email";
				if ($notes        != $j["notes"])        $changes[] = "notes";
				if (!count($changes)) {
					$description = "<i>no change</i>";
				} else {
					$description = join(", ", $changes) . " updated";
				}
				//$description = $nickname;
				$firstname    = $j["firstname"];
				$lastname     = $j["lastname"];
				$nickname     = $j["nickname"];
				$organization = $j["organization"];
				$title        = $j["title"];
				$address1     = $j["address1"];
				$address2     = $j["address2"];
				$zip          = $j["zip"];
				$phone        = $j["phone"];
				$fax          = $j["fax"];
				$cell         = $j["cell"];
				$email        = $j["email"];
				$notes        = $j["notes"];
			}
			$output = '<tr bgcolor="#FFFFFF" class="helptext"><td><a href="/staff/view.php?id=' . $j["created_user"] . '">' . $j["createdName"] . '</a></td><td>' . $description . '</td><td align="right">' . format_date($j["created_date"]) . '</td></tr>' . $output;
		}
		echo $output;
		echo "</table>";
	}
echo drawBottom();?>