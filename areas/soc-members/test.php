<?php
include('../include.php');

drawTop();

if ($posting) {
	langTranslatePost('string');
	die(draw_array($_POST));
}

$f = new form('foobar');
$f->set_field(array('type'=>'textarea', 'name'=>'string', 'default'=>'hello world'));
echo $f->draw();

drawBottom();
?>