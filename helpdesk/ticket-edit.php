<?php
include("include.php");

if ($posting) {
	if (!$module_admin) $_POST["user_id"] = $_SESSION["user_id"];	
	format_post_nulls("type_id, priorityID");
	db_query("UPDATE helpdesk_tickets SET 
		created_user = {$_POST["user_id"]},
		title = '{$_POST["title"]}',
		description = '{$_POST["description"]}',
		type_id = {$_POST["type_id"]},
		departmentID = {$_POST["departmentID"]},
		priorityID = {$_POST["priorityID"]}
		WHERE id = " . $_GET["id"]);
	url_change("ticket.php?id=" . $_GET["id"]);
}

echo drawTop();

$t = db_grab("SELECT created_user, title, description, type_id, departmentID, priorityID FROM helpdesk_tickets t WHERE t.id = " . $_GET["id"]);

$form = new intranet_form;
if ($module_admin) $form->addUser("user_id",  "Posted By" , $t["created_user"], false);
$form->addRow("itext",  "Problem" , "title", $t["title"], "", true);
if ($module_admin) {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities", $t["priorityID"]);
} else {
	$form->addRow("select", "Priority" , "priorityID", "SELECT id, description FROM helpdesk_tickets_priorities WHERE is_admin = 0", $t["priorityID"]);
}
$form->addRow("select", "Department" , "departmentID", "SELECT departmentID, shortName FROM departments WHERE isHelpdesk = 1", $t["departmentID"], true, 50, "updateTypes(this.value)");
$form->addRow("select", "Type" , "type_id", "SELECT id, description FROM helpdesk_tickets_types WHERE departmentID = " . $t["departmentID"] . " ORDER BY description", $t["type_id"]);
$form->addRow("textarea", "Description" , "description", $t["description"], "", true);
$form->addRow("submit"  , "save changes");
$form->draw("Edit Ticket");
drawBottom();
?>