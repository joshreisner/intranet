<?php
include("../../include.php");

if ($posting) {
	if ($uploading) list($_POST["type_id"], $_POST["content"]) = file_get_uploaded("userfile", "docs_types");
	$id = db_save("policy_docs");
	url_change("../?category=" . $_POST["categoryID"]);
}


echo drawTop();

$types = db_query("SELECT description, extension FROM docs_types ORDER BY description");
$extensions = $doctypes = $array = array();

while ($t = db_fetch($types)) {
	$extensions[] = '(extension != "' . $t["extension"] . '")';
	$doctypes[] = " - " . $t["description"] . " (." . $t["extension"] . ")";
	$array[] = "'" . $t["extension"] . "'";
}
$array = implode(",", $array);

echo draw_javascript("
	function suggestName(which) {
		//var types = new Array(" . $array . ");
		var fileParts   = which.value.split('.');
		var extension	= fileParts.pop();
		var filename	= fileParts.join(' ');

		if (!url_id()) which.form.name.value = format_title(filename);
	}
	
	function validate() {
		return true;
	}
");
$form = new intranet_form;

//function addRow($type, $title, $name="", $value="", $default="", $required=false, $maxlength=50, $onchange=false) {

$form->addRow("file", "Document", "userfile", "", "", true, false, "suggestName(this);");
$form->addRow("itext",  "Name", "name", @$r["name"], "", true, 50);
$form->addRow("select", "Category", "categoryID", "SELECT id, description FROM policy_categories ORDER BY description", @$r["categoryID"], true);

$form->addRow("submit",   "Save Changes");
if (isset($_GET["id"])) {
	$form->draw("<a href='/policy/?category=1'>Policy</a> > Edit Policy Document");
} else {
	$form->draw("<a href='/policy/?category=1'>Policy</a> > Add New Policy Document");
}

echo drawBottom();
?>