<?
$included = !@include("../../include.php");
if ($posting) {
	$id = db_save("press_clips");
	url_change_post("/press-clips/clip.php?id=" . $id);
} elseif ($included) {
	$_josh["request"]["path_query"] = "/" . $location . "/edit/"; //shoddy way of setting the form target
	$r["url"] = "http://";
} elseif (url_id()) {
	drawTop();
	$r = db_grab("SELECT id, title, url, publication, pub_date, description, type_id from press_clips WHERE id = " . $_GET["id"]);
	$r["title"] = format_title($r["title"], "US");
} else {
	echo drawTop();
	$r["title"] = format_title($_GET["title"], "US");
	$r["url"] = $_GET["url"];
	$url = url_parse($r["url"]);

	if ($url["domainname"] == "nytimes") {
		$r["publication"] = "NY Times";
		$r["title"] = str_replace("- Nytimes.com", "", $r["title"]);
	} elseif ($url["domainname"] == "latimes") {
		$r["publication"] = "LA Times";
		$r["title"] = str_replace(" - Los Angeles Times", "", $r["title"]);
	} elseif ($url["domainname"] == "washingtonpost") {
		$r["publication"] = "Washington Post";
		//$r["title"] = str_replace("The Associated Press: ", "", $r["title"]);
	} elseif ($url["domainname"] == "reuters") {
		$r["publication"] = "Reuters";
		//$r["title"] = str_replace("The Associated Press: ", "", $r["title"]);
	} elseif (($url["domainname"] == "google") && ($url["subfolder"] == "afp")) {
		$r["publication"] = "AFP";
		$r["title"] = str_replace("Afp: ", "", $r["title"]);
	} elseif (($url["domainname"] == "google") && ($url["subfolder"] == "ap")) {
		$r["publication"] = "AP";
		$r["title"] = str_replace("The Associated Press: ", "", $r["title"]);
	}
}

//to control return_to redirects.  i'm not sure how i should handle this generally.  it's a problem mainly when the page is included
if ($referrer && ($referrer["host"] == $request["host"])) $_josh["referrer"] = false;

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