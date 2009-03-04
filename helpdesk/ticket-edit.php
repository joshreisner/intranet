<?php
include("include.php");

if ($posting) {
	if (!$isAdmin) $_POST["userID"] = $_SESSION["userID"];	
	format_post_nulls("typeID, priorityID");
	db_query("UPDATE helpdesk_tickets SET 
		createdBy = {$_POST["userID"]},
		title = '{$_POST["title"]}',
		description = '{$_POST["description"]}',
		typeID = {$_POST["typeID"]},
		departmentID = {$_POST["departmentID"]},
		priorityID = {$_POST["priorityID"]}
		WHERE id = " . $_GET["id"]);
	url_change("ticket.php?id=" . $_GET["id"]);
}

echo drawTop();

$t = db_grab("SELECT createdBy, title, description, typeID, departmentID, priorityID FROM helpdesk_tickets t WHERE t.id = " . $_GET["id"]);

$form = new intranet_form;
if ($isAdmin) $form->addUser("userID",  "Posted By" , $t["createdBy"], false);
$form->addRow("itext",  "Problem" , "title", $t["title"], "", true);
if ($isAdmin) {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities", $t["priorityID"]);
} else {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities WHERE isAdmin = 0", $t["priorityID"]);
}
$form->addRow("select", "Department" , "departmentID", "SELECT departmentID, shortName FROM intranet_departments WHERE isHelpdesk = 1", $t["departmentID"], true, 50, "updateTypes(this.value)");
$form->addRow("select", "Type" , "typeID", "SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = " . $t["departmentID"] . " ORDER BY description", $t["typeID"]);
$form->addRow("textarea", "Description" , "description", $t["description"], "", true);
$form->addRow("submit"  , "save changes");
$form->draw("Edit Ticket");
drawBottom();
?>