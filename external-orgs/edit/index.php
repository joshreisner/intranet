<?
$included = !@include("../../include.php");

if ($posting) {
	$id = db_save("external_orgs");
	db_checkboxes("types", "external_orgs_to_types", "org_id", "type_id", $id);
	url_change("/" . $location . "/?type=" . db_grab("SELECT type_id FROM external_orgs_to_types WHERE org_id = " . $id)); //pure hackery
} elseif ($included) {
	$_josh["request"]["path_query"] = "/" . $location . "/edit/"; //shoddy way of setting the form target
	$r["url"] = "http://";
} else {
	url_query_require();
	drawTop();
	$r = db_grab("SELECT id, name, url, description from external_orgs WHERE id = " . $_GET["id"]);
}

$form = new intranet_form;
$form->addRow("itext",  "Name", "name", @$r["name"], "", true, 255);
$form->addCheckboxes("types", "Types", "external_orgs_types", "external_orgs_to_types", "org_id", "type_id", @$_GET["id"]);
$form->addJavascript("form_checkboxes_empty(form, 'types')", "a 'Type' must be checked");
$form->addRow("itext",  "URL", "url", @$r["url"], "", true, 255);
$form->addJavascript("form.url.value == 'http://'", "the 'URL' field is empty");
$form->addRow("textarea-plain", "Description", "description", @$r["description"], "", true);
if ($included) {
	//we are on the index page
	$form->addRow("submit", "Add Org");
	$form->draw("Add New Organization");
} else {
	//we are on this here page
	$form->addRow("submit", "Save Changes");
	$form->draw("Edit Organization");
}

if (!$included) drawBottom();
?>