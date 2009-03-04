<?
include("../include.php");

$d = db_grab("SELECT 
		a.title, 
		t.extension, 
		a.content 
	FROM wiki_topics_attachments a 
	JOIN intranet_doctypes t ON a.typeID = t.id
	WHERE a.id = " . $_GET["id"]);

//db_query("INSERT INTO documents_views ( documentID, userID, viewedOn ) VALUES ( {$_GET["id"]}, {$_SESSION["user_id"]}, GETDATE() )");

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header("Content-Length: " . strlen($d["content"]));
header("Content-Disposition: attachment; filename=" . format_file_name($d["title"], $d["extension"]));
echo ($d["content"]);

db_close();
?>