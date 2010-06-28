<?php
include("include.php");

if (url_action('delete')) {
	db_delete('bb_topics_types');
	url_drop('action,id');
}

echo drawTop();

$t = new table('bb_topics_types', drawHeader((($page['is_admin']) ? array('category_edit.php'=>getString('category_new')) : false)));
$t->set_column('category', 'l', getString('category'));
$t->set_column('topics', 'r', getString('topics'));
if ($page['is_admin']) $t->set_column('delete', 'd', '&nbsp;');
$result = db_table('SELECT 
		y.id, 
		y.title' . langExt() . ' category, 
		(SELECT COUNT(*) FROM bb_topics t WHERE t.type_id = y.id AND t.is_active = 1) topics 
	FROM bb_topics_types y 
	WHERE y.is_active = 1
	ORDER BY y.title');
foreach ($result as &$r) {
	$r['category'] = draw_link('category.php?id=' . $r['id'], $r['category']);
	if ($page['is_admin']) $r['delete'] = draw_img('/images/icons/delete.png', url_query_add(array('action'=>'delete', 'id'=>$r['id']), false));
}
echo $t->draw($result, 'No categories added yet');

echo drawBottom();
?>