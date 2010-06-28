<?php
include("include.php");

echo drawTop();

$t = new table('bb_topics_types', drawHeader());
$t->set_column('category', 'l', getString('category'));
$t->set_column('topics', 'r', getString('topics'));
$result = db_table('SELECT 
		y.id, 
		y.title' . langExt() . ' category, 
		(SELECT COUNT(*) FROM bb_topics t WHERE t.type_id = y.id AND t.is_active = 1) topics 
	FROM bb_topics_types y 
	ORDER BY y.title');
foreach ($result as &$r) $r['category'] = draw_link('category.php?id=' . $r['id'], $r['category']);
echo $t->draw($result, 'No categories added yet');

echo drawBottom();
?>