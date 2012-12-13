<?php include("include.php");

if ($posting) {
	$user_id = ($page['is_admin']) ? $_POST["user_id"] : $_SESSION["user_id"];
	format_post_nulls("type_id");
	$id = db_query("INSERT INTO helpdesk_tickets (
    	created_user,
    	type_id,
		priorityID,
		departmentID,
		description,
		statusID,
		ipAddress,
		created_date,
		updated_date,
		title
	) VALUES (
		" . $user_id . ",
		" . $_POST["type_id"] . ",
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
	emailITTicket($id, 'new'); //special for carla
	url_change('ticket.php?id=' . $id);
}

echo drawTop();

echo drawMessage($helpdeskStatus, "center");
?>

<script language="javascript">
	<!--
	function updateTypes(departmentID) {
		var types = new Array(3, 8);
		<?php
		$types = db_query("SELECT id, departmentID, description FROM helpdesk_tickets_types ORDER BY departmentID, description");
		$options = array();
		while ($t = db_fetch($types)) {
			$options[$t["departmentID"]][] = '"' . $t["id"] . '|' . $t["description"] . '"';
		}
		while (list($key, $value) = each($options)) {?>
			types[<?php echo $key?>] = new Array(<?php echo implode(",", $value)?>);
		<?php }?>
		var field = document.getElementById("type_id").options;
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


	<?php
echo drawTableStart();
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
						t.created_date,
						t.created_user,
						t.id,
						ISNULL(u2.nickname, u2.firstname) owner,
						ISNULL(u.nickname, u.firstname) firstname,
						u.lastname lastname
					FROM helpdesk_tickets t
					JOIN helpdesk_tickets_statuses	s  ON t.statusID = s.id
					JOIN users				u  ON t.created_user = u.id
					JOIN departments		d  ON t.departmentID = d.departmentID
					LEFT JOIN users		u2 ON t.ownerID = u2.id
					WHERE (t.statusID <> 9 OR t.statusID IS NULL) $department
					ORDER BY d.shortName, t.created_date DESC");
	$lastDept = "";
	$count = db_found($result);
	if ($count) {
		echo drawHeaderRow($deptName . " Open Tickets", 4, "new", "#bottom");
		?>
	<tr>
		<th width="50%" align="left">Short Description</th>
		<th width="15%" align="left"><nobr>Submitted By</nobr></th>
		<th width="20%" align="left">Status</th>
		<th width="15%"><nobr>Assigned To</nobr></th>
	</tr>
	<?php while ($r = db_fetch($result)) {
		if ($r["department"] != $lastDept) {
			$lastDept = $r["department"];
			$count = db_grab("SELECT COUNT(*) tickets FROM helpdesk_tickets WHERE departmentID = " . $r["departmentID"] . " AND statusID <> 9");
			?>
		<tr class="group">
			<td colspan="4"><?php echo $lastDept?> Tickets (<?php echo $count ?>)</td>
		</tr>
		<?php }
		if (($r["departmentID"] == 2) && !$page['is_admin'] && ($r["created_user"] != $_SESSION["user_id"])) {
			//ticket not clickable in this scenario
			?>
		<tr height="32" class="thread">
			<td class="input"><?php echo $r["title"]?></td>
			<td><nobr><?php echo $r["firstname"]?> <?php echo substr($r["lastname"], 0, 1)?>.</nobr></td>
			<td><?php echo $r["description"]?></td>
			<td align="center"><?php echo $r["owner"]?></td>
		</tr>
		<?php } else { ?>
		<tr height="32" class="thread" onclick="location.href='ticket.php?id=<?php echo $r["id"]?>';">
			<td class="input"><a href="ticket.php?id=<?php echo $r["id"]?>"><?php echo $r["title"]?></a></td>
			<td><nobr><?php echo $r["firstname"]?> <?php echo substr($r["lastname"], 0, 1)?>.</nobr></td>
			<td><?php echo $r["description"]?></td>
			<td align="center"><?php echo $r["owner"]?></td>
		</tr>
		<?php }
	 }
} else {
	echo drawHeaderRow("No Tickets", 1, "new", "#bottom");
	echo drawEmptyResult("There are no open $deptName tickets right now!", 4);
}?>
</table>

<a name="bottom"></a>
<?php
$form = new intranet_form;
if ($page['is_admin']) $form->addUser("user_id",  "Posted By" , $_SESSION["user_id"], false);
$form->addRow("itext",  "Problem" , "title", "", "", true);
if ($page['is_admin']) {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities", 3);
} else {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities WHERE is_admin <> 1", 3);
}
$form->addRow("select", "Department" , "departmentID", "SELECT departmentID, shortName FROM departments WHERE isHelpdesk = 1 ORDER BY shortName", $departmentID, true, 50, "updateTypes(this.value)");
$form->addRow("select", "Type" , "type_id", "SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = $departmentID ORDER BY description");
$form->addRow("textarea", "Description" , "description", "", "", true);
$form->addRow("submit"  , "report problem");
$form->draw("Add a New Ticket");

echo drawBottom(); 
?>