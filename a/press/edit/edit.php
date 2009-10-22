<?php
include("../../include.php");

if ($posting) {
	$theuser_id = ($page['is_admin']) ? $_POST["created_user"] : $_SESSION["user_id"];
	db_query("UPDATE press_releases SET
			headline       = '{$_POST["headline"]}',	
			detail         = '{$_POST["detail"]}',	
			location       = '{$_POST["location"]}',	
			text           = '" . format_html($_POST["text"]) . "',	
			corporationID = {$_POST["corporationID"]},
			updated_date     = GETDATE(),
			updated_user     = {$theuser_id}
			WHERE id = " . $_GET["id"]);
	url_change("../?id=" . $_GET["id"]);
}

drawTop();

$r = db_grab("SELECT id, headline, detail, location, releaseDate, corporationID, text FROM press_releases WHERE id = " . $_GET["id"]);
	
$form = new intranet_form;
if ($page['is_admin']) $form->addUser("created_user",  "Posted By" , $_SESSION["user_id"], false, "EEDDCC");
$form->addRow("itext",  "Headline" , "headline", $r["headline"], "", true, 255);
$form->addRow("itext",  "Detail" , "detail", $r["detail"], "", false, 255);
$form->addRow("itext",  "Location" , "location", $r["location"], "", true, 255);
$form->addRow("select", "Organization" , "corporationID", "SELECT id, title from organizations ORDER BY title", $r["corporationID"]);
$form->addRow("date",  "Date" , "releaseDate", $r["releaseDate"]);
$form->addRow("textarea", "Text" , "text", $r["text"], "", true);
$form->addRow("submit"  , "update press release");
$form->draw("Update Release");

drawBottom();?>