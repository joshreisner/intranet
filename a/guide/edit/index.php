<?php
include('../../../include.php');

if ($posting) {
	langTranslatePost('content');
	$id = db_save('guide', 1);
	url_change('../');
}

echo drawTop();

$f = new form('guide', 1);
echo $f->draw();

echo drawBottom();
?>