<?php
include('../../include.php');

if ($posting) {
	format_post_bits('is_selected');
	langTranslatePost('description');
	$id = db_save('soc_members');
	url_change_post('../');
}

echo drawTop();

$f = new form('soc_members', @$_GET['id'], $page['name']);
$f->set_field(array('name'=>'country', 'type'=>'select', 'sql'=>'SELECT id, en FROM jr_countries ORDER BY en'));
langUnsetFields($f, 'description');
langTranslateCheckbox($f);
$f->set_title_prefix(draw_link('../', 'Institutional Members') . ' &gt; ');
echo $f->draw();

echo drawBottom();


?>