<?php
include("../../include.php");

if ($posting) {
	$id = db_save("web_news_blurbs");
	url_change_post("../");
} elseif (url_action('delete')) {
	db_delete($_GET['id']);
	url_change('../');
}

echo drawTop();

$f = new form("web_news_blurbs", @$_GET["id"]);
$f->set_title(drawHeader());
echo $f->draw();

echo drawBottom();
?>