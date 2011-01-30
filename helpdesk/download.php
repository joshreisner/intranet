<?
include("../include.php");

$d = db_grab("SELECT 
		d.title,
		d.extension, 
		d.content 
	FROM helpdesk_tickets_attachments d 
	WHERE d.id = " . $_GET["id"]);

//db_query("INSERT INTO docs_views ( documentID, user_id, viewedOn ) VALUES ( {$_GET["id"]}, {$_SESSION["user_id"]}, GETDATE() )");

file_download($d["content"], $d["title"], $d["extension"])
?>