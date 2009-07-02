<?php
include("../include.php");

echo drawTop();

$blurbs = db_table("SELECT b.id, b.title, b.publish_date FROM web_news_blurbs b ORDER BY publish_date DESC");

$t = new table("web_news_blurbs");
$t->col("title");
$t->col("publish_date", "r");
$t->set_title(drawHeader(array("add new"=>"edit/")));

foreach ($blurbs as &$b) {
	$b["title"] = draw_link("edit/?id=" . $b["id"], $b["title"]);
	$b["publish_date"] = format_date($b["publish_date"]);
}

echo $t->draw($blurbs);

echo drawBottom();
?>