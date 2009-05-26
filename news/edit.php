<?php
if (isset($_josh)) { //included
	$_josh["request"]["path_query"] = "/news/edit.php";
} else { //page loaded on its own
	include("../include.php");
	if ($posting) {
		if (isset($_FILES["content"]["name"]) && !empty($_FILES["content"]["name"])) {
			list($_POST["content"], $_POST["filetype_id"]) = file_get_uploaded("content");
		}
		if (isset($_FILES["image"]["name"]) && !empty($_FILES["image"]["name"])) {
			list($_POST["image"], $_POST["imagetype_id"]) = file_get_uploaded("image");
		}
		$id = db_save("news_stories");
		db_checkboxes("corporationID", "news_stories_to_organizations", "newsID", "organizationID", $id);
		url_change("./?id=" . $id);
	}	
	drawTop();
	$r = db_grab("SELECT 
		n.headline,
		n.outlet,
		n.pubdate,
		n.url,
		n.description
		FROM news_stories n
		WHERE id = " . $_GET["id"]);
}

$form = new intranet_form;
//addCheckboxes($name, $desc, $table, $linking_table=false, $table_col=false, $link_col=false, $id=false, $admin=false) {
$form->addCheckboxes("corporationID", "Organization", "organizations", "news_stories_to_organizations", "newsID", "organizationID", @$_GET["id"]);
$form->addRow("itext", "Headline", "headline", @$r["headline"], "", true, 255);
$form->addRow("itext", "News Outlet", "outlet", @$r["outlet"], "", true, 255);
$form->addRow("date", "Date", "pubdate", @$r["pubdate"], "", true);
$form->addRow("file", "Image<br>(optional)", "image", "", "", false);
$form->addRow("file", "File<br>(optional)", "content", "", "", true);
$form->addRow("itext", "URL<br>(optional)", "url", @$r["url"], "", false, 255);
$form->addRow("textarea-plain", "Description<br>(optional)", "description", @$r["description"]);
$form->addRow("submit", "Save Changes");

if (url_id()) {
	$form->draw("<a href='/news/'>In the News</a> &gt; Edit Story");
	drawBottom();
} else {
	$form->draw("Add New Story");
}
?>