<?
include("../include.php");

$docs = db_query("SELECT id, name FROM documents");

while ($d = db_fetch($docs)) {
	$fname = $root . "data/documents/data/" . $d["id"] . ".dat";
	if ($file = @file_get_contents($fname)) {
		echo $d["name"] . " size is " . format_size(strlen($file));
		echo " (" . format_file_size($fname) . ")<br>";
		db_query("UPDATE documents SET content = " . format_binary($file) . " WHERE id = " . $d["id"]);
		echo strlen(format_binary($file)) . "<hr>";
	} else {
		db_query("UPDATE documents SET isActive = 0 WHERE id = " . $d["id"]);
	}
}
?>