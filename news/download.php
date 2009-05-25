<?
include("../include.php");

$d = db_grab("SELECT 
		n.headline, 
		t.extension, 
		n.content 
	FROM news_stories n
	JOIN docs_types t ON n.filetype_id = t.id
	WHERE n.id = " . $_GET["id"]);

file_download($d["content"], $d["headline"], $d["extension"])
?>