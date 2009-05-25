<?
include("../include.php");

$docs = db_query("SELECT id, title FROM docs");

while ($d = db_fetch($docs)) {
	$ftitle = $root . "data/docs/data/" . $d["id"] . ".dat";
	if ($file = @file_get_contents($ftitle)) {
		echo $d["title"] . " size is " . format_size(strlen($file));
		echo " (" . format_file_size($ftitle) . ")<br>";
		db_query("UPDATE docs SET content = " . format_binary($file) . " WHERE id = " . $d["id"]);
		echo strlen(format_binary($file)) . "<hr>";
	} else {
		db_query("UPDATE docs SET is_active = 0 WHERE id = " . $d["id"]);
	}
}
?>