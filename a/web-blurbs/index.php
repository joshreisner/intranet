<?php
include("../include.php");

if (url_id() && url_action('delete')) {
	db_delete('web_news_blurbs', $_GET['id']);
	url_drop('action,id');
}

echo drawTop();

$blurbs = db_table("SELECT b.id, b.title, b.publish_date FROM web_news_blurbs b WHERE b.is_active = 1 ORDER BY b.publish_date DESC");

$t = new table("web_news_blurbs");
$t->col("title");
$t->col("publish_date", "r");
$t->col('delete', 'c', '&nbsp;');
$t->set_title(drawHeader(array("add new"=>"edit/")));

foreach ($blurbs as &$b) {
	$b["title"] = draw_link("edit/?id=" . $b["id"], $b["title"]);
	$b["publish_date"] = format_date($b["publish_date"]);
	$b['delete'] = draw_img('/images/icons/delete.gif', url_query_add(array('action'=>'delete', 'id'=>$b['id']), false));
}

echo $t->draw($blurbs);

echo drawBottom();
?>