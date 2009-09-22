<?php
include('../../include.php');

if ($posting) {
	$id = db_save('jr_members_inst');
	url_change_post('../');
}

echo drawTop();

$f = new form('jr_members_inst', @$_GET['id'], 'Edit Institutional Member');
$f->set_field(array('name'=>'country', 'type'=>'select', 'sql'=>'SELECT id, en FROM jr_countries ORDER BY en'));
$f->set_title_prefix(draw_link('../', 'Institutional Members') . ' &gt; ');
echo $f->draw();

echo drawBottom();


?>