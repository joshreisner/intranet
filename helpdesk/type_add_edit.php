<?php include("include.php");

if ($posting) {
	$id = db_save("helpdesk_tickets_types");
	url_change("type.php?id=" . $id);
}

echo drawTop();


if (isset($_GET["id"])) {
	$result = db_query("SELECT description FROM helpdesk_tickets_types WHERE id = " . $_GET["id"]);
	$r = db_fetch($result);
}

$form = new intranet_form;
$form->addRow("itext", "Type Name" , "description", @$r["description"]);
if (isset($_GET["id"])) {
	$form->addRow("submit"  , "Save Changes");
	$form->draw("Edit Ticket Type");
} else {
	$form->addRow("submit"  , "Add Type");
	$form->draw("Add New Ticket Type");
}

echo drawBottom();
?>