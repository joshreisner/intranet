<?php
include('../../include.php');

if ($posting) {
	$id = db_save('soc_whatsnew');
	url_change_post('../');
}

echo drawTop();

$f = new form('soc_whatsnew', @$_GET['id'], $page['name']);
$f->set_title_prefix(drawHeader(false, ' '));
echo $f->draw();

echo drawBottom();


?>