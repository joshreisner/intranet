<?php
include('../../include.php');

if ($posting) {
	$id = db_save('soc_whatsnew');
	url_change_post('../');
}

echo drawTop();

$f = new form('soc_whatsnew', @$_GET['id'], 'Edit News Item');
$f->set_title_prefix(draw_link('../', 'What\'s New') . ' &gt; ');
echo $f->draw();

echo drawBottom();


?>