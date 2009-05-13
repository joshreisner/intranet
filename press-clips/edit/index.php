<?
$included = !@include("../../include.php");
if ($posting) {
	$id = db_save("press_clips");
	url_change("/press-clips/clip.php?id=" . $id);
} elseif ($included) {
	$_josh["request"]["path_query"] = "/" . $location . "/edit/"; //shoddy way of setting the form target
	$r["url"] = "http://";
} elseif (url_id()) {
	drawTop();
	$r = db_grab("SELECT id, title, url, publication, pub_date, description, type_id from press_clips WHERE id = " . $_GET["id"]);
} else {
	echo drawTop();
	$r["title"] = $_GET["title"];
	$r["url"] = $_GET["url"];
}

$form = new intranet_form;
$form->addRow("itext",  "Title", "title", @$r["title"], "", true, 255);
$form->addRow("itext",  "URL", "url", @$r["url"], "", true, 255);
$form->addJavascript("form.url.value == 'http://'", "the 'URL' field is empty");
$form->addRow("itext",  "Publication", "publication", @$r["publication"], "", true, 255);
$form->addRow("select",  "Type", "type_id", "SELECT id, title FROM press_clips_types", @$r["type_id"], true);
$form->addRow("date",  "Date", "pub_date", @$r["pub_date"], "", true);
$form->addRow("textarea", "Description", "description", @$r["description"], "", true);
if ($included) {
	//we are on the index page
	$form->addRow("submit", "Add Clip");
	$form->draw("Add New Press Clip");
} else {
	//we are on this here page
	$form->addRow("submit", "Save Changes");
	$form->draw("Edit Press Clip");
}

if (!$included) drawBottom();
?>