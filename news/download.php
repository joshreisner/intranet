<?
include("../include.php");

$d = db_grab("SELECT 
		n.headline, 
		t.extension, 
		n.content 
	FROM news_stories n
	JOIN documents_types t ON n.fileTypeID = t.id
	WHERE n.id = " . $_GET["id"]);

file_download($d["content"], $d["headline"], $d["extension"])
?>