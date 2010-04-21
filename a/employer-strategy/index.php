<?php
include("../../include.php");

echo drawTop();

$blurbs = db_table("SELECT 
		r.id,
		r.title,
		t.icon,
		ISNULL(r.updated_date, r.created_date) updated
		FROM employer_strategy_resources r
		JOIN docs_types t ON r.type_id = t.id
		WHERE r.is_active = 1
		ORDER BY updated DESC", 20);

$t = new table("web_news_blurbs");
$t->set_column("icon");
$t->set_column("title");
$t->set_column("updated", "r");
$t->set_title(drawHeader(array("add new"=>"edit/")));

foreach ($blurbs as &$b) {
	$b["icon"] = draw_img($b["icon"]);
	$b["title"] = draw_link("edit/?id=" . $b["id"], $b["title"]);
	$b["updated"] = format_date($b["updated"]);
}

echo $t->draw($blurbs);

echo drawBottom();
?>