<?
include("../include.php");

$d = db_grab("SELECT 
		d.name, 
		t.extension, 
		d.content 
	FROM documents d 
	JOIN documents_types t ON d.typeID = t.id
	WHERE d.id = " . $_GET["id"]);

db_query("INSERT INTO documents_views ( documentID, userID, viewedOn ) VALUES ( {$_GET["id"]}, {$_SESSION["user_id"]}, GETDATE() )");

file_download($d["content"], $d["name"], $d["extension"])
?>