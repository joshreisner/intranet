<?php
include("../include.php");

echo drawTop();




$result = db_table("SELECT id, title, updated_date FROM objects");

$t = new table();
$t->col("title");
$t->col("updated_date");

foreach ($result as &$r) {
	$r["updated_date"] = format_date($r["updated_date"]);
}

echo $t->draw($result, "no objects!");

$f = new form("objects");

echo $f->draw();




echo drawBottom();
?>