<?php
include("../../include.php");

if ($posting) {
	if ($uploading) {
		$type = getDocTypeID($_FILES["userfile"]["name"]);
		$content = format_binary(file_get($_FILES["userfile"]["tmp_name"]));
		@unlink($_FILES["userfile"]["tmp_name"]);
	}

	if (url_id()) {
		if ($uploading) {
			db_query("UPDATE policy_docs SET 
				name = '{$_POST["name"]}',
				typeID = {$type},
				categoryID = {$_POST["categoryID"]},
				content = $content,
				updated_date = GETDATE(),
				updated_user = {$_SESSION["user_id"]}
				WHERE id = " . $_GET["id"]);
		} else {
			db_query("UPDATE policy_docs SET 
				name = '{$_POST["name"]}',
				categoryID = {$_POST["categoryID"]},
				updated_date = GETDATE(),
				updated_user = {$_SESSION["user_id"]}
				WHERE id = " . $_GET["id"]);
		}
	} else {
		$_GET["id"] = db_query("INSERT into policy_docs (
			name,
			typeID,
			categoryID,
			content,
			created_date,
			created_user,
			is_active
		) VALUES (
			'" . $_POST["name"] . "',
			"  . $type . ",
			" . $_POST["categoryID"] . ",
			"  . $content . ",
			GETDATE(),
			"  . $_SESSION["user_id"] . ",
			1
		)");
	}

	//db_checkboxes("doc", "docs_to_categories", "documentID", "categoryID", $_GET["id"]);
	url_change("../?category=" . $_POST["categoryID"]);
}


drawTop();

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

drawBottom();?>