<?php
include('../../include.php');

if ($posting) {
	langTranslatePost('title,description');
	db_save('pages');
	url_change_post('./');
}

echo drawTop();

$f = new form('pages', @$_GET['id']);
$f->set_field(array('type'=>'text', 'name'=>'title' . langExt(), 'label'=>getString('title')));
$f->set_field(array('type'=>'textarea', 'class'=>'tinymce', 'name'=>'description' . langExt(), 'label'=>getString('description')));
$f->unset_fields('is_admin,is_hidden,url');
if (url_id('module_id')) $f->set_field(array('type'=>'hidden', 'name'=>'module_id', 'value'=>$_GET['module_id']));
if (url_id('modulette_id')) $f->set_field(array('type'=>'hidden', 'name'=>'modulette_id', 'value'=>$_GET['modulette_id']));
if (isset($_GET['url'])) $f->set_field(array('type'=>'hidden', 'name'=>'url', 'value'=>$_GET['url']));
langUnsetFields($f, 'title,description');
langTranslateCheckbox($f);
echo $f->draw();

echo drawBottom();
?>