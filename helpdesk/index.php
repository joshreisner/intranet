<?php include("include.php");

if ($posting) {
	$user_id = ($isAdmin) ? $_POST["userID"] : $_SESSION["user_id"];
	format_post_nulls("typeID");
	$r = db_query("INSERT INTO helpdesk_tickets (
    	createdBy,
    	typeID,
		priorityID,
		departmentID,
		description,
		statusID,
		ipAddress,
		createdOn,
		updatedOn,
		title
	) VALUES (
		" . $user_id . ",
		" . $_POST["typeID"] . ",
		'" . $_POST["priorityID"] . "',
		'" . $_POST["departmentID"] . "',
		'" . $_POST["description"] . "',
		1,
		'{$_SERVER["REMOTE_ADDR"]}',
		GETDATE(),
		GETDATE(),
		'" . $_POST["title"] . "'
    );");
    
    //$r = db_grab("SELECT MAX(id) id FROM helpdesk_tickets");
	//todo - email mohammed for critical
	emailITTicket($r, "new"); //special for carla
	url_change();
}

drawTop();

echo drawServerMessage($helpdeskStatus, "center");
?>

<script language="javascript">
	<!--
	function updateTypes(departmentID) {
		var types = new Array(3, 8);
		<?
		$types = db_query("SELECT id, departmentID, description FROM helpdesk_tickets_types ORDER BY departmentID, description");
		$options = array();
		while ($t = db_fetch($types)) {
			$options[$t["departmentID"]][] = '"' . $t["id"] . '|' . $t["description"] . '"';
		}
		while (list($key, $value) = each($options)) {?>
			types[<?=$key?>] = new Array(<?=implode(",", $value)?>);
		<? }?>
		var field = document.getElementById("typeID").options;
		field.length = 0;
		field[i] = new Option("", "");
		for (var i = 0; i < types[departmentID].length; i++) {
			var value = types[departmentID][i].split("|");
			field[i + 1] = new Option(value[1], value[0]);
		}
		return true;
	}
	//-->
</script>

<table class="left" cellspacing="1">
	<?php
	if (url_id("dept")) {
		$department = " AND t.departmentID = " . $_GET["dept"];
		$deptName = db_grab("SELECT shortName FROM departments WHERE departmentID = " . $_GET["dept"]);
	} else {
		$department = "";
		$deptName = "";
	}
	
	$result = db_query("SELECT
						t.title,
						s.description,
						t.departmentID,
						d.shortName department,
						t.createdOn,
						t.createdBy,
						t.id,
						ISNULL(u2.nickname, u2.firstname) owner,
						ISNULL(u.nickname, u.firstname) firstname,
						u.lastname lastname
					FROM helpdesk_tickets t
					JOIN helpdesk_tickets_statuses	s  ON t.statusID = s.id
					JOIN users				u  ON t.createdBy = u.userID
					JOIN departments		d  ON t.departmentID = d.departmentID
					LEFT JOIN users		u2 ON t.ownerID = u2.userID
					WHERE (t.statusID <> 9 OR t.statusID IS NULL) $department
					ORDER BY d.shortName, t.createdOn DESC");
	$lastDept = "";
	$num = db_found($result);
	if ($num) {
		echo drawHeaderRow($deptName . " Open Tickets", 4, "new", "#bottom");
		?>
	<tr>
		<th width="50%" align="left">Short Description</th>
		<th width="15%" align="left"><nobr>Submitted By</nobr></th>
		<th width="20%" align="left">Status</th>
		<th width="15%"><nobr>Assigned To</nobr></th>
	</tr>
	<? while ($r = db_fetch($result)) {
		if ($r["department"] != $lastDept) {
			$lastDept = $r["department"];
			$count = db_grab("SELECT COUNT(*) tickets FROM helpdesk_tickets WHERE departmentID = " . $r["departmentID"] . " AND statusID <> 9");
			?>
		<tr class="group">
			<td colspan="4"><?=$lastDept?> Tickets (<?=$count ?>)</td>
		</tr>
		<? }
		if (($r["departmentID"] == 2) && !$isAdmin && ($r["createdBy"] != $_SESSION["user_id"])) {
			//ticket not clickable in this scenario
			?>
		<tr height="32" class="thread">
			<td class="input"><?=$r["title"]?></td>
			<td><nobr><?=$r["firstname"]?> <?=substr($r["lastname"], 0, 1)?>.</nobr></td>
			<td><?=$r["description"]?></td>
			<td align="center"><?=$r["owner"]?></td>
		</tr>
		<? } else { ?>
		<tr height="32" class="thread"
			onclick		= "location.href='ticket.php?id=<?=$r["id"]?>';"
			onmouseover	= "javascript:aOver('id<?=$r["id"]?>');"
			onmouseout	= "javascript:aOut('id<?=$r["id"]?>');">
			<td class="input"><a href="ticket.php?id=<?=$r["id"]?>" id="id<?=$r["id"]?>"><?=$r["title"]?></a></td>
			<td><nobr><?=$r["firstname"]?> <?=substr($r["lastname"], 0, 1)?>.</nobr></td>
			<td><?=$r["description"]?></td>
			<td align="center"><?=$r["owner"]?></td>
		</tr>
		<? }
	 }
} else {
	echo drawHeaderRow("No Tickets", 1, "new", "#bottom");
	echo drawEmptyResult("There are no open $deptName tickets right now!", 4);
}?>
</table>

<a name="bottom"></a>
<?
$form = new intranet_form;
if ($isAdmin) $form->addUser("userID",  "Posted By" , $_SESSION["user_id"], false);
$form->addRow("itext",  "Problem" , "title", "", "", true);
if ($isAdmin) {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities", 3);
} else {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities WHERE isAdmin <> 1", 3);
}
$form->addRow("select", "Department" , "departmentID", "SELECT departmentID, shortName FROM departments WHERE isHelpdesk = 1 ORDER BY shortName", $departmentID, true, 50, "updateTypes(this.value)");
$form->addRow("select", "Type" , "typeID", "SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = $departmentID ORDER BY description");
$form->addRow("textarea", "Description" , "description", "", "", true);
$form->addRow("submit"  , "report problem");
$form->draw("Add a New Ticket");

drawBottom(); 
?>