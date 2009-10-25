<?php
include('../../include.php');

if ($posting) {
	langTranslatePost('title');
	$id = db_save('soc_whatsnew');
	url_change_post('../');
}

echo drawTop();

$f = new form('soc_whatsnew', @$_GET['id'], $page['title']);
langUnsetFields($f, 'title');
langTranslateCheckbox($f);
$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
$f->set_field(array('type'=>'text', 'name'=>'link', 'label'=>getString('link')));
$f->set_title_prefix($page['breadcrumbs']);
echo $f->draw();

echo drawBottom();


?>