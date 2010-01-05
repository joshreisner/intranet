<?php
include('../../include.php');

echo drawTop();

$f = new form('translate', false, $page['title']);
$f->set_field(array('type'=>'textarea', 'name'=>'text_to_translate', 'class'=>'tinymce', 'label'=>'English text', 'value'=>@$_POST['text_to_translate']));
$f->set_title_prefix($page['breadcrumbs']);
echo $f->draw();

if ($posting) {
	echo draw_div_class('message', language_translate(@$_POST['text_to_translate'], 'en', 'es'));
	echo draw_div_class('message', language_translate(@$_POST['text_to_translate'], 'en', 'fr'));
	echo draw_div_class('message', language_translate(@$_POST['text_to_translate'], 'en', 'ru'));
}

echo drawBottom();
?>