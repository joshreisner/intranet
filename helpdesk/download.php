<?
include("../include.php");

$d = db_grab("SELECT 
		d.title,
		t.extension, 
		d.content 
	FROM helpdesk_tickets_attachments d 
	JOIN intranet_doctypes t ON d.typeID = t.id
	WHERE d.id = " . $_GET["id"]);

//db_query("INSERT INTO documents_views ( documentID, userID, viewedOn ) VALUES ( {$_GET["id"]}, {$_SESSION["user_id"]}, GETDATE() )");

file_download($d["content"], $d["title"], $d["extension"])
?>