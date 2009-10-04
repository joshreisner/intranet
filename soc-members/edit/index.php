<?php
include('../../include.php');

if ($posting) {
	format_post_bits('is_selected');
	$id = db_save('jr_members_inst');
	url_change_post('../');
}

echo drawTop();

$f = new form('jr_members_inst', @$_GET['id'], $page['name']);
$f->set_field(array('name'=>'country', 'type'=>'select', 'sql'=>'SELECT id, en FROM jr_countries ORDER BY en'));
$f->set_title_prefix(draw_link('../', 'Institutional Members') . ' &gt; ');
echo $f->draw();

echo drawBottom();


?>