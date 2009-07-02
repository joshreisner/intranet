<?php
include("../../include.php");

if ($posting) {
	$id = db_save("web_news_blurbs");
	url_change_post("../");
}

echo drawTop();

$f = new form("web_news_blurbs", true, @$_GET["id"]);
$f->set_title(drawHeader());
echo $f->draw();

echo drawBottom();
?>