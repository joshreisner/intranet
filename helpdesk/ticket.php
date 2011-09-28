<?php
include("include.php");

url_query_require();

if (!$r = db_grab('SELECT
		u.id,
		t.title,
		t.created_user,
		t.description,
		t.timeSpent,
		t.ipAddress,
		t.ownerID,
		ISNULL(u.nickname, u.firstname) first,
		(SELECT COUNT(*) FROM helpdesk_tickets_attachments a WHERE a.ticketID = t.id) attachments,
		u.lastname last,
		u.officeID,
		o.name office,
		t.created_date,
		t.statusID,
		t.type_id,
		t.departmentID,
		t.priorityID,
		p.is_admin is_adminPriority,
		t.closed_date,
		y.description type,
		s.is_active is_activeOwner,
		ISNULL(s.nickname, s.firstname) ownerFirst,
		MONTH(t.created_date) createdMonth,
		YEAR(t.created_date) createdYear
	FROM helpdesk_tickets t
	JOIN users					u ON t.created_user	= u.id
	LEFT JOIN helpdesk_tickets_priorities	p ON t.priorityID	= p.id
	JOIN offices				o ON u.officeID		= o.id
	LEFT JOIN users			s ON t.ownerID		= s.id
	LEFT JOIN helpdesk_tickets_types	y ON t.type_id		= y.id
	WHERE t.id = ' . $_GET['id'])) url_change('./');

if ($r["statusID"] != 9) { //open
	$typeRequired = false;
} elseif (!$r["type_id"]) { //closed, no type
	$typeRequired = false;
} else {
	$typeRequired = true;
}

//$page['is_admin'] = ($page['is_admin'] && ($r["departmentID"] == $_SESSION["departmentID"])) ? true : false;

if ($uploading) {
	//upload an attachment
	$_POST['content'] = file_get_uploaded('userfile');
	$_POST['extension'] = file_ext($_FILES['userfile']['name']);
	$_POST["ticketID"] = $_GET["id"];
	$id = db_save('helpdesk_tickets_attachments', false);
	url_change();
} elseif ($posting) {
	//add a comment
	//auto-assign ticket if unassigned and followup poster is an IT admin
	$followupAdmin = (!empty($_POST['is_admin'])) ? 1 : 0;
	if ($page['is_admin'] && !$followupAdmin && empty($r["ownerID"])) {
		//set to it staff assigned if no status
		if ($r["statusID"] == 1) $r["statusID"] = 2;
		db_query('UPDATE helpdesk_tickets SET ownerID = ' . user() . ', statusID = ' . $r["statusID"] . ', updated_date = GETDATE() WHERE id = ' . $_GET['id']);
	}
	
	//insert followup
	db_query('INSERT INTO helpdesk_tickets_followups (
				ticketID, 
				created_user, 
				created_date, 
				message,
				is_admin
			) VALUES (
				' . $_GET["id"] . ',
				' . $_SESSION["user_id"] . ',
				GETDATE(),
				\'' . $_POST["message"] . '\',
				' . $followupAdmin . '
			)');
	
	//email and exit
	if ($followupAdmin) {
		emailITticket($_GET["id"], "followupadmin");
	} else {
		emailITticket($_GET["id"], "followup");
	}
	url_change();
}

echo drawTop();

echo drawMessage($helpdeskStatus, "center");

//populate dropdowns
$timeSpentOptions = array(0=>0, 15=>15, 30=>30, 45=>45, 60=>60, 75=>75, 90=>90, 105=>105, 120=>"Two Hours", 180=>"Three Hours", 240=>"Four Hours", 300=>"Five Hours", 360=>"Six Hours", 420=>"Seven Hours", 480=>"Eight Hours", 540=>"Nine Hours", 600=>"10 Hours", 660=>"11 Hours", 720=>"12 Hours", 780=>"13 Hours", 940=>"14 Hours", 1000=>"15 Hours", 1060=>"16 Hours");
if (!$r["timeSpent"]) $r["timeSpent"] = 0;
if (!isset($timeSpentOptions[$r["timeSpent"]])) {
	$timeSpentOptions[$r["timeSpent"]] = $r["timeSpent"];
	sort($timeSpentOptions);
}

if ($r["ownerID"] && !$r["is_activeOwner"]) {
	/* this is for if the ticket assignee has left before you are viewing this ticket
	interesting possibility would be to show all active owners for that time period */
	$ownerOptions[$r["ownerID"]] = $r["ownerFirst"];
	ksort($ownerOptions);
}

//load code for JS
$extensions = array();
$doctypes = array();
$types = db_query("SELECT description, extension FROM docs_types ORDER BY description");
while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t["extension"] . '")';
	$doctypes[] = " - " . $t["description"] . " (." . $t["extension"] . ")";
}

?>
<script language="javascript">
	<!--
	function validate(form) {
		tinyMCE.triggerSave();
		if (!form.message.value.length || (form.message.value == '<p>&nbsp;</p>')) return false;
		return true;
	}
	
	function validateAttachment(form) {
		if (!form.title.value.length) {
			alert("Please enter a name for the attachment.");
			return false;
		}
		if (!form.userfile.value.length) {
			alert("Please select a file to upload.");
			return false;
		} else {
			var arrFile   = form.userfile.value.split(".");
			var extension = arrFile[arrFile.length - 1].toLowerCase();
			if (<?=implode(" && ", $extensions)?>) {
				alert("Only these filetypes are supported by this system:\n\n <?=implode("\\n", $doctypes)?>\n\nPlease change your selection, or make sure that the \nappropriate extension is at the end of the filename.");
				return false;
			}
		}
		return true;
	}
	
	function newStatus(status) {
		if (status == 9) {
		<? if ($r["timeSpent"] && $r["type_id"]) {?>
			location.href='<?=$request["path_query"]?>&ticketID=<?=$_GET["id"]?>&newStatus=' + status;
		<? } else { ?>
			document.all["statusID<?=$r["statusID"]?>"].selected = true;
			alert("In order to close a ticket, you have to select a Type and an amount of Time Spent");
		<? } ?>
		} else {
			location.href='<?=$request["path_query"]?>&ticketID=<?=$_GET["id"]?>&newStatus=' + status;
		}
	}
	//-->
</script>

<table class="left" cellspacing="1">
	<?
	if ($r["statusID"] != 9) {
		$nextTicketID			= false;
		$lastTicketID			= false;
		$counter				= 0;
		$counterLastTicketID	= false;
		$counterCheckNext		= false;
		$tickets = db_query("SELECT id FROM helpdesk_tickets WHERE statusID <> 9 ORDER BY created_date DESC");
		while ($t = db_fetch($tickets)) {
			$counter++;
			if ($_GET["id"] == $t["id"]) {
				$lastTicketID = $counterLastTicketID;
				$counterCheckNext = true;
				$ticketCount = $counter;
			} elseif ($counterCheckNext) {
				$nextTicketID = $t["id"];
				$counterCheckNext = false;
			} else {
				//echo $t["ticketID"] . " <> " . $_GET["id"] . "<br>";
			}
			$counterLastTicketID = $t["id"];
		}
		$title = "View Open Ticket (" . $ticketCount . " of " . $counter . ")";

		if ($page['is_admin']) {
			if ($lastTicketID && $nextTicketID) {
				echo drawHeaderRow($title, 2, "prev", "ticket.php?id=" . $lastTicketID, "next", "ticket.php?id=" . $nextTicketID);
			} elseif ($lastTicketID) {
				echo drawHeaderRow($title, 2, "prev", "ticket.php?id=" . $lastTicketID, "next", "");
			} elseif ($nextTicketID) {
				echo drawHeaderRow($title, 2, "prev", "", "next", "ticket.php?id=" . $nextTicketID);
			} else {
				echo drawHeaderRow($title, 2);
			}
		} else {
			echo drawHeaderRow($title, 2, "add a followup message","#bottom");
		}
	} else {
		echo drawHeaderRow("View Ticket", 2, "add a followup message","#bottom");
	}
	
	if ($page['is_admin']) {?>
	<form name="ticketForm">
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Status</td>
		<td><?=draw_form_select("statusID", "SELECT id, description FROM helpdesk_tickets_statuses", $r["statusID"], true, "", "newStatus(this.value);")?></td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Posted By</td>
		<td><?
		$sql = ($_josh["db"]["language"] == "mssql") ? "SELECT u.id, u.lastname + ', ' + ISNULL(u.nickname, u.firstname) FROM users u WHERE u.is_active = 1 ORDER BY u.lastname, ISNULL(u.nickname, u.firstname)" : "SELECT u.id, CONCAT(u.lastname, ', ', IFNULL(u.nickname, u.firstname)) FROM users u WHERE u.is_active = 1 ORDER BY u.lastname, IFNULL(u.nickname, u.firstname)";
		echo draw_form_select("postedBy", $sql, $r["created_user"], true, "", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newUser=' + this.value");
		?>
		<a href="user.php?id=<?=$r["created_user"]?>">view all</a> / <a href="user.php?id=<?=$r["created_user"]?>&month=<?=$r["createdMonth"]?>&year=<?=$r["createdYear"]?>">this month</a>
		</td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Location</td>
		<td><?=$r["office"]?>
		<a href="office.php?id=<?=$r["officeID"]?>">view all</a> / <a href="office.php?id=<?=$r["officeID"]?>&month=<?=$r["createdMonth"]?>&year=<?=$r["createdYear"]?>">this month</a>
		</td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Assigned To</td>
		<td>
			<?
			echo draw_form_select("ownerID", $ownerOptions, $r["ownerID"], false, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newOwner=' + this.value", false);
			if ($r["ownerID"]) {?>
			<a href="admin.php?id=<?=$r["ownerID"]?>">view all</a> / <a href="admin.php?id=<?=$r["ownerID"]?>&month=<?=$r["createdMonth"]?>&year=<?=$r["createdYear"]?>">this month</a>
			<? }?>
		</td>
	</tr>
	<tr class="helpdesk-hilite" height="30">
		<td class="left">Time Spent</td>
		<td><?=draw_form_select("timeSpent", $timeSpentOptions, $r["timeSpent"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newTime=' + this.value", false);?> minutes</td>
	</tr>
	<? } elseif ($r["ownerID"]) {?>
	<tr height="30">
		<td class="left">Assigned To</td>
		<td><a href="/staff/view.php?id=<?=$r["ownerID"]?>"><?=$r["ownerFirst"]?></a></td>
	</tr>
	<? }?>
	<!-- <tr height="30">
		<td class="left">Ticket Number</td>
		<td><?=$_GET["id"]?></td>
	</tr> -->
	<tr height="30">
		<td class="left">Ticket Age</td>
		<td><?=format_time_business($r["created_date"], $r["closed_date"]);?></td>
	</tr>
	<tr height="30">
		<td class="left">Type</td>
		<td><?=draw_form_select("type_id", "SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = " . $r["departmentID"] . " ORDER BY description", $r["type_id"], $typeRequired, false, "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newType=' + this.value");?>
			<? if ($page['is_admin']) {
			 if ($r["type_id"]) {
			 	echo '<a href="type.php?id=' . $r["type_id"] . '">view all</a> / <a href="type.php?id=' . $r["type_id"] . '&month=' . $r["createdMonth"] . '&year=' . $r["createdYear"] . '">this month</a>';
			 } else {
			 	echo '<a href="types.php">edit types</a>';
			 }
			 }?>
		</td>
	</tr>
	<tr height="30">
		<td class="left">Department</td>
		<td><?=draw_form_select("departmentID", "SELECT departmentID, shortName FROM departments WHERE isHelpdesk = 1", $r["departmentID"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newDepartment=' + this.value", false);?></td>
	</tr>
	<tr height="30">
		<td class="left">Priority</td>
		<td><?
		if ($page['is_admin'] || $r["is_adminPriority"]) {
			echo draw_form_select("priorityID", "SELECT id, description FROM helpdesk_tickets_priorities", $r["priorityID"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newPriority=' + this.value");
		} else {
			echo draw_form_select("priorityID", "SELECT id, description FROM helpdesk_tickets_priorities WHERE is_admin = 0", $r["priorityID"], true, "field", "location.href='" . $request["path_query"] . "&ticketID=" . $_GET["id"] . "&newPriority=' + this.value");
		}
		?></td>
	</tr>
	<? if ($r["ipAddress"]) {?>
	<tr height="30">
		<td class="left">IP Address</td>
		<td><?=$r["ipAddress"]?></td>
	</tr>
	<? }
	
	if ($r["attachments"]) {?>
	<tr height="30">
		<td class="left"><?=format_quantitize($r['attachments'], 'Attachment')?></td>
		<td>
			<table class="nospacing">
			<?
				$attachments = db_table('SELECT
				id,
				title,
				extension
			FROM helpdesk_tickets_attachments
			WHERE ticketID = ' . $_GET["id"]);
		foreach ($attachments as $a) {?>
			<tr height="21">
				<td width="18"><?=file_icon($a['extension'], 'download.php?id=' . $a["id"])?></td>
				<td><a href="download.php?id=<?=$a["id"]?>"><?=$a["title"]?></a></td>
			</tr>
		<? } ?>
		</table>
		</td>
	</tr>
	<? } ?>
	</form>
	<? 
$editurl = ($page['is_admin']) ? "ticket-edit.php?id=" . $_GET["id"] : false;
echo drawThreadTop($r["title"], $r["description"], $r["created_user"], $r["first"] . " " . $r["last"], $r["created_date"], $editurl);

$result = db_query("SELECT
					u.id,
					f.message,
					ISNULL(u.nickname, u.firstname) first,
					u.lastname last,
					f.created_date,
					f.is_admin
				FROM helpdesk_tickets_followups	f
				JOIN users			u ON f.created_user	= u.id
				WHERE f.ticketID = " . $_GET['id'] . "
				ORDER BY f.created_date");
while ($r = db_fetch($result)) {
	if ($r['is_admin'] && !$page['is_admin']) continue;
	echo drawThreadComment($r["message"], $r["id"], $r["first"] . " " . $r["last"], $r["created_date"], $r["is_admin"]);
}

echo drawThreadCommentForm(true);

echo '</table>';

?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Attach Document", 2);?>
	<form enctype="multipart/form-data" action="<?=$request["path_query"]?>" method="post" onsubmit="javascript:return validateAttachment(this);">
	<tr>
		<td class="left">Document Name</td>
		<td><?=draw_form_text("title",  @$d["name"])?></td>
	</tr>
	<tr>
		<td class="left">File</td>
		<td><input type="file" name="userfile" size="40" class="field" value=""></td>
	</tr>
	<tr>
		<td class="bottom" align="center" colspan="2"><?=draw_form_submit("Attach Document");?></td>
	</tr>
	</form>
</table>
<? 
echo drawBottom(); ?>