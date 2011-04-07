<?php
include('../../include.php');

if ($posting) {
	if ($_GET['doc_id'] == 'new') $_GET['doc_id'] = false;
	if ($uploading) {
		$_POST['extension'] = file_type($_FILES['content']['name']);
		$_POST['content'] = file_get_uploaded('content');
	}	
	$id = db_save('dl_docs', @$_GET['doc_id']);
	db_checkboxes('categories', 'dl_docs_to_categories', 'doc_id', 'category_id', $id);
	url_drop('id');
} elseif (url_action('delete')) {
	db_delete('dl_docs');
	url_drop('id,action');
}

echo drawTop();

if (!empty($_GET['doc_id'])) {
	if ($_GET['doc_id'] == 'new') $_GET['doc_id'] = false;
	$f = new form('dl_docs', @$_GET['doc_id'], ($_GET['doc_id'] ? 'Edit' : 'Add') . ' Document');
	$f->set_title_prefix($page['breadcrumbs']);
	$f->set_field(array('name'=>'title', 'label'=>getString('title'), 'type'=>'text'));
	$f->unset_fields('extension');
	$f->set_field(array('name'=>'content', 'label'=>getString('file'), 'type'=>'file', 'additional'=>getString('upload_max') . file_get_max()));
	$f->set_field(array('name'=>'categories', 'label'=>getString('categories'), 'type'=>'checkboxes', 'options_table'=>'dl_categories', 'option_title'=>'title', 'linking_table'=>'dl_docs_to_categories', 'object_id'=>'doc_id', 'option_id'=>'category_id'));
	echo $f->draw(); 
} else {
	$result = db_table('SELECT 
					d.id, 
					d.title, 
					' . db_updated('d') . ', 
					d.extension,
					c.title "group"
			FROM dl_docs d
			JOIN dl_docs_to_categories d2c ON d.id = d2c.doc_id
			JOIN dl_categories c ON d2c.category_id = c.id
			ORDER BY c.precedence, d.title;');
	
	$links = ($page['is_admin']) ? array(url_query_add(array('doc_id'=>'new'), false)=>getString('add_new')) : false;
	
	$t = new table('dl_docs', drawHeader($links));
	$t->set_column('icon', 'd', '&nbsp;');
	$t->set_column('title', 'l', getString('title'));
	$t->set_column('updated', 'r', getString('updated'));
	
	foreach ($result as &$r) {
		$link = 'info.php?id=' . $r['id'];
		$r['icon'] = file_icon($r['extension'], $link);
		$r['title'] = draw_link($link, $r['title']);
		if (getOption('languages')) $r['title'] .= ' (' . $r['language'] . ')';
		$r['updated'] = format_date($r['updated']);
	}
	
	echo $t->draw($result, getString('documents_empty'));
}
						
echo drawBottom();