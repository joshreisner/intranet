<?php
include("../include.php");

$table = "objects";

if ($posting) {
	$id = db_save($table);
	url_change();
}

echo drawTop();

$result = db_table("SELECT id, title, created_date FROM " . $table);

$t = new table();
$t->set_title("Last 40 Objects");
$t->set_column("title");
$t->set_column("created_date", "r");

foreach ($result as &$r) {
	$r["title"] = draw_link("./?id=" . $r["id"], $r["title"]);
	$r["created_date"] = format_date($r["created_date"]);
}

echo $t->draw($result, "no objects!");


$f = new form($table, true);
$f->title();
echo $f->draw();

echo drawBottom();
?>