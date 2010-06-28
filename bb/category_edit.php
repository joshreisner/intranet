<?php
include('include.php');

if ($posting) {
	langTranslatePost('title');
	$id = db_save('bb_topics_types');
	url_change('categories.php');
}

echo drawTop();

$f = new form('bb_topics_types', @$_GET['id'], $page['breadcrumbs'] . $page['title']);
langUnsetFields($f, 'title');
langTranslateCheckbox($f, url_id());
echo $f->draw(false, false);



echo drawBottom();
?>