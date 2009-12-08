<?	include("../include.php");

if (url_action("delete")) {
	db_delete('docs');
	url_drop();
}

echo drawTop();

if (getOption('languages')) {
	$result = db_table('SELECT 
				d.id, 
				d.title' . langExt() . ' title, 
				' . db_updated('d') . ', 
				i.icon, 
				i.description alt, 
				c.title' . langExt() . ' "group",
				l.title language
			FROM docs d
			JOIN docs_to_categories d2c ON d.id = d2c.documentID
			JOIN docs_categories c ON d2c.categoryID = c.id
			JOIN docs_types i ON d.type_id = i.id
			JOIN languages l ON d.language_id = l.id
			' . getChannelsWhere('docs', 'd', 'doc_id') . '
			ORDER BY c.precedence, d.title;');
} else {
	$result = db_table('SELECT d.id, d.title, ' . db_updated('d') . ', i.icon, i.description alt, c.title "group"
			FROM docs d
			JOIN docs_to_categories d2c ON d.id = d2c.documentID
			JOIN docs_categories c ON d2c.categoryID = c.id
			JOIN docs_types i ON d.type_id = i.id
			' . getChannelsWhere('docs', 'd', 'doc_id') . '
			ORDER BY c.precedence, d.title;');
}

$links = ($page['is_admin']) ? array('edit.php'=>getString('add_new')) : false;
$t = new table('docs', drawHeader($links));
$t->set_column('icon', 'd', '&nbsp;');
$t->set_column('title');
$t->set_column('updated', 'r');

foreach ($result as &$r) {
	$link = 'info.php?id=' . $r['id'];
	$r['icon'] = draw_img($r['icon'], $link);
	$r['title'] = draw_link($link, $r['title']);
	if (getOption('languages')) $r['title'] .= ' (' . $r['language'] . ')';
	$r['updated'] = format_date($r['updated']);
}

echo $t->draw($result, getString('documents_empty'));
						
echo drawBottom();
?>