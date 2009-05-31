<?php
include("../../include.php");

if ($posting) {
	db_save("pages");
	if (!db_grab("SELECT homePageID FROM modules WHERE id = " . $_POST["module_id"])) {
		//set homepage of module with current page if null
		db_query("UPDATE modules SET homePageID = {$_GET["id"]} WHERE id = " . $_POST["module_id"]);
	}
	url_change_post();
}

drawTop();

$r = db_grab("SELECT
	p.id,
	p.name title,
	p.helpText,
	m.id module_id,
	m.title module,
	p.is_admin,
	p.isInstancePage
	FROM pages p
	JOIN modules m ON p.module_id = m.id
	WHERE p.id = " . $_GET["id"]);

$form = new intranet_form;
$form->addRow("itext",  "Title", "name", $r["title"], "", true, 50);
$form->addRow("checkbox",  "Is Admin", "is_admin", $r["is_admin"], "", true, 50);
$form->addRow("checkbox",  "Hide in Nav?", "isInstancePage", $r["isInstancePage"], "", true, 50);
$form->addRow("select", "Module", "module_id", "SELECT id, title FROM modules WHERE is_active = 1 ORDER BY title", $r["module_id"], $r["module_id"]);
$form->addRow("textarea", "Help Text", "helpText", $r["helpText"]);
$form->addRow("submit",   "Save Changes");
$form->draw("Edit Page Info");


drawBottom();
?>