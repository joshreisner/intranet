<?php
include('../../include.php');

if ($posting) {
	db_save('pages');
	if (!db_grab('SELECT homePageID FROM modules WHERE id = ' . $_POST['module_id'])) {
		//set homepage of module with current page if null
		db_query('UPDATE modules SET homePageID = ' . $_GET['id'] . ' WHERE id = ' . $_POST['module_id']);
	}
	url_change_post();
}

drawTop();

$f = new form('pages', @$_GET['id']);
$f->set_field(array('name'=>'module_id', 'type'=>'select', 'sql'=>'SELECT id, title FROM modules WHERE is_active = 1 ORDER BY title'));
echo $f->draw();

drawBottom();
?>