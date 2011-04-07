<?php
include('../../include.php');

if ($posting) {
	if ($_GET['category_id'] == 'new') $_GET['category_id'] = false;
	$id = db_save('dl_categories', @$_GET['category_id']);
	url_drop('category_id');
} elseif (url_action('delete')) {
	db_delete('dl_categories');
	url_drop('action,id');
}

echo drawTop();

if (!empty($_GET['category_id'])) {
	//category form
	if ($_GET['category_id'] == 'new') $_GET['category_id'] = false;
	$f = new form('dl_categories', $_GET['category_id'], ($_GET['category_id'] ? 'Edit' : 'Add') . ' Category');
	$f->set_title_prefix($page['breadcrumbs']);
	echo $f->draw();
} else {
	//list of categories
	$result = db_table('SELECT id, title, ' . db_updated() . ' FROM dl_categories WHERE is_active = 1 ORDER BY precedence');
	
	$links = ($page['is_admin']) ? array(url_query_add(array('category_id'=>'new'), false)=>getString('add_new')) : false;
	
	$t = new table('dl_categories', drawHeader($links));
	$t->set_column('draggy', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('updated', 'r', getString('updated'));
	$t->set_column('delete', 'd', '&nbsp;');
	
	foreach ($result as &$r) {
		$r['draggy'] = draw_img('/images/icons/move.png');
		$r['title'] = draw_link(url_query_add(array('category_id'=>$r['id']), false), $r['title']);
		$r['updated'] = format_date($r['updated']);
		$r['delete'] = draw_link(url_query_add(array('action'=>'delete', 'id'=>$r['id']), false), 'x', false, 'confirm');
	}
	
	echo $t->draw($result, getString('categories_empty'));
}
						
echo drawBottom();