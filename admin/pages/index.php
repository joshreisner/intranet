<? include("../../include.php");

if ($posting) {
	format_post_bits("isInstancePage, is_admin");
	db_query("UPDATE pages SET 
		name = '{$_POST["title"]}',
		is_admin = {$_POST["is_admin"]},
		isInstancePage = {$_POST["isInstancePage"]},
		module_id = '{$_POST["module_id"]}',
		helpText = '{$_POST["helpText"]}'
		WHERE id = " . $_GET["id"]);
	if (!db_grab("SELECT homePageID FROM modules WHERE id = " . $_POST["module_id"])) {
		//set homepage of module with current page if null
		db_query("UPDATE modules SET homePageID = {$_GET["id"]} WHERE id = " . $_POST["module_id"]);
	}
	url_change($_POST["returnTo"]);
}

drawTop();

$r = db_grab("SELECT
	p.id,
	p.name title,
	p.helpText,
	m.id module_id,
	m.name module,
	p.is_admin,
	p.isInstancePage
	FROM pages p
	JOIN modules m ON p.module_id = m.id
	WHERE p.id = " . $_GET["id"]);

$form = new intranet_form;
$form->addRow("hidden",  "", "returnTo", $_GET["returnTo"]);
$form->addRow("itext",  "Title", "title", $r["title"], "", true, 50);
//$form->addRow("itext",  "Precedence", "precedence", $r["precedence"], "", true, 50);
$form->addRow("checkbox",  "Is Admin", "is_admin", $r["is_admin"], "", true, 50);
$form->addRow("checkbox",  "Hide in Nav?", "isInstancePage", $r["isInstancePage"], "", true, 50);
//$form->addRow("checkbox",  "Is Secure", "isSecure", $r["isSecure"], "", true, 50);
$form->addRow("select", "Module", "module_id", "SELECT id, name FROM modules WHERE is_active = 1 ORDER BY name", $r["module_id"], $r["module_id"]);
//$form->addRow("text", "Module", "", "<span class='" . str_replace("/", "", $r["url"]) . " block'>" . $r["module"] . "</span>");
$form->addRow("textarea", "Help Text", "helpText", $r["helpText"]);
$form->addRow("submit",   "Save Changes");
$form->draw("Edit Page Info");


drawBottom();?>