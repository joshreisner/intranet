<?php
$pageIsPublic = true;
include('../include.php');

echo draw_h3('Converting Character Sets');

$charset = 'utf8';
$collation = 'utf8_general_ci';

//fix charset and collation globally
$result = db_query('SELECT
	c.table_name, 
	c.column_name, 
	c.character_set_name, 
	c.collation_name
FROM information_schema.columns c
WHERE c.table_schema = "' . $_josh['db']['database'] . '" AND (
	c.character_set_name <> "' . $charset . '" OR 
	c.collation_name <> "' . $collation . '"
)');

if (db_found($result)) {
	while ($r = db_fetch($result)) {
		$column = $r['table_name'] . '.' . $r['column_name'];
		if (db_query('ALTER TABLE ' . $_josh['db']['database'] . '.' . $r['table_name'] . ' DEFAULT CHARSET=' . $charset . ', MODIFY COLUMN ' . $column . ' text CHARACTER SET ' . $charset . ' COLLATE ' . $collation)) {
			echo 'successfully changed ' . $column . ' from ' . $r['character_set_name'] . ' / ' . $r['collation_name'] . ' to ' . $charset . ' / ' . $collation;
		} else {
			echo 'error changing ' . $column . ' from ' . $r['character_set_name'] . ' / ' . $r['collation_name'] . ' to ' . $charset . ' / ' . $collation;
		}
		echo '<br>';
	}
} else {
	echo 'nothing needs to be changed!';
}

//add missing tables
echo draw_h3('Add Missing Tables');

echo '<ul>';

db_table_drop('modules, cal_events_types');

$tables = file_folder('tables/', '.sql');
foreach ($tables as $t) {
	if (!db_table_exists($t['base'])) {
		$sql = str_ireplace('latin1', $charset, file_get($t['path_name']));
		$sql = str_ireplace('MyISAM', 'InnoDB', $sql);
		$statements = explode(';', $sql);
		foreach ($statements as $s) if (strlen(trim($s))) db_query($s);
		echo draw_li('created ' . $t['base']);
	} else {
		echo draw_li($t['base'] . ' already present');
	}
}
echo '</ul>';

//add missing columns
echo draw_h3('Add Missing Columns');

echo '<ul>';

$columns = array(
	array('table'=>'bb_followups', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'bb_followups', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'bb_followups', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'bb_followups', 'column'=>'precedence', 'type'=>'int'),
	
	array('table'=>'bb_topics', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'bb_topics', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'bb_topics', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'bb_topics', 'column'=>'precedence', 'type'=>'int'),
	
	array('table'=>'cal_events', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'cal_events', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'cal_events', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'cal_events', 'column'=>'precedence', 'type'=>'int'),
	
	array('table'=>'docs', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'docs', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'docs', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'docs', 'column'=>'precedence', 'type'=>'int'),
	
	array('table'=>'pages', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'pages', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'pages', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'pages', 'column'=>'precedence', 'type'=>'int'),

	array('table'=>'users', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'users', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'users', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'users', 'column'=>'precedence', 'type'=>'int'),

	array('table'=>'users_requests', 'column'=>'publish_date', 'type'=>'datetime'),
	array('table'=>'users_requests', 'column'=>'publish_user', 'type'=>'int'),
	array('table'=>'users_requests', 'column'=>'is_published', 'type'=>'checkbox'),
	array('table'=>'users_requests', 'column'=>'precedence', 'type'=>'int'),

	array('table'=>'bb_topics', 'column'=>'replies', 'type'=>'int'),
	array('table'=>'docs', 'column'=>'language_id', 'type'=>'int'),
	array('table'=>'modules', 'column'=>'folder', 'type'=>'text'),
	array('table'=>'modules', 'column'=>'color', 'type'=>'text'),
	array('table'=>'modules', 'column'=>'hilite', 'type'=>'text'),
	array('table'=>'organizations', 'column'=>'precedence', 'type'=>'int'),
	array('table'=>'pages', 'column'=>'modulette_id', 'type'=>'int'),
	array('table'=>'users', 'column'=>'image_small', 'type'=>'image'),
	array('table'=>'users', 'column'=>'image_medium', 'type'=>'image'),
	array('table'=>'users', 'column'=>'language_id', 'type'=>'int'),
	array('table'=>'users_requests', 'column'=>'created_user', 'type'=>'int'),
	array('table'=>'users_requests', 'column'=>'updated_user', 'type'=>'int'),
	array('table'=>'users_requests', 'column'=>'updated_date', 'type'=>'datetime'),
	array('table'=>'users_requests', 'column'=>'is_active', 'type'=>'checkbox')
);

foreach ($columns as $c) {
	extract($c);
	if (!db_column_exists($table, $column)) {
		db_column_add($table, $column, $type);
		echo draw_li($column . ' was added to ' . $table);
	} else {
		echo draw_li($column . ' already existed in ' . $table);
	}
}

echo '</ul>';

//rename old columns
echo draw_h3('Rename Old Columns');

echo '<ul>';


//columns to be renamed
$columns = array(
	array('table'=>'channels', 'before'=>'title_en', 'after'=>'title'),
	array('table'=>'links', 'before'=>'text', 'after'=>'title'),
	array('table'=>'pages', 'before'=>'isInstancePage', 'after'=>'is_hidden'),
	array('table'=>'pages', 'before'=>'name', 'after'=>'title'),
	array('table'=>'pages', 'before'=>'helpText', 'after'=>'description'),
	array('table'=>'users', 'before'=>'image', 'after'=>'image_large')
);

foreach ($columns as $c) {
	extract($c);
	if (!db_column_exists($table, $after)) {
		db_column_rename($table, $before, $after);
		echo draw_li($table . '.' . $before . ' was renamed to ' . $after);
	} else {
		echo draw_li($after . ' already existed in ' . $table);
	}
}

echo '</ul>';

//fix column types
echo draw_h3('Fix Column Types');
echo '<ul>';
$tables = db_tables();
foreach ($tables as $t) {
	$columns = db_columns($t);
	//die(draw_array($columns));
	if (!stristr($t, '_to_') && !stristr($t, '_2_')
		&& ($t != 'it_system_status')
		&& ($t != 'docs_views')
		&& ($t != 'ldcodes')
		&& ($t != 'queries_executions')
		&& ($t != 'web_income_tables_values')
		
		&& ($columns[0]['type'] == 'int') && (!$columns[0]['auto'])) {
		//set first column to PRIMARY KEY AUTO_INCREMENT
		db_column_key($t, $columns[0]['name']);
		echo draw_li('set ' . $t . '.' . $columns[0]['name'] . ' to primary key auto_increment');
	}
	
	$replacements = array('bit'=>'tinyint', 'longtext'=>'text', 'mediumblob'=>'mediumblob');
	foreach ($columns as $c) {
		if (isset($replacements[$c['type']])) {
			db_column_type_set($t, $c['name'], $replacements[$c['type']]);
			echo draw_li('set ' . $t . '.' . $c['name'] . ' from ' . $c['type'] . ' to ' . $replacements[$c['type']]);
		}
	}
}

echo '</ul>';

//fix table data
echo draw_h3('Fix Table Data');

echo '<ul>';


//null passwords
db_query('UPDATE users SET language_id = 1, password = NULL');
db_query('UPDATE users SET email = "josh@joshreisner.com" WHERE id = 1');
echo draw_li('passwords have been nulled');


//fix images
$users = db_table('SELECT id, image_large FROM users WHERE image_large IS NOT NULL');
foreach ($users as $u) {
	db_query('UPDATE users SET 
			image_medium = ' . format_binary(format_image_resize($u['image_large'], 135)) . ',
			image_small = ' . format_binary(format_image_resize($u['image_large'], 50)) . '	
		WHERE id = ' . $u['id']);
}
echo draw_li('images have been fixed');


//pages url should only be page name
db_query('UPDATE pages SET is_active = 1');
$pages = db_table('SELECT id, url FROM pages WHERE url LIKE "%/%"');
if ($pages) {
	foreach ($pages as $p) {
		$pageparts = explode('/', $p['url']);
		$p['url'] = trim($pageparts[count($pageparts)-1]);
		db_query('UPDATE pages SET url = "' . $p['url'] . '" WHERE id = ' . $p['id']);
	}
	echo draw_li('corrected ' . count($pages) . ' pages');
} else {
	echo draw_li('pages are all correct');
}

db_query('UPDATE bb_topics t SET t.replies = (SELECT COUNT(*) FROM bb_followups f WHERE f.topic_id = t.id AND f.is_active = 1)');
echo draw_li('bb_topics.replies column populated');

echo '</ul>';
?>