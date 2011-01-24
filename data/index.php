<?php
$pageIsPublic = true;
include('../include.php');

echo draw_h2('Converting Character Sets');

$charset = 'utf8';
$collation = 'utf8_general_ci';

//get incorrect tables
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

echo draw_h2('Creating Missing Tables');

echo '<ul>';

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

//columns to be added
$columns = array(
	array('table'=>'docs', 'column'=>'language_id', 'type'=>'int'),
	array('table'=>'modules', 'column'=>'folder', 'type'=>'text'),
	array('table'=>'modules', 'column'=>'color', 'type'=>'text'),
	array('table'=>'modules', 'column'=>'hilite', 'type'=>'text'),
	array('table'=>'pages', 'column'=>'modulette_id', 'type'=>'int'),
	array('table'=>'users', 'column'=>'image_small', 'type'=>'image'),
	array('table'=>'users', 'column'=>'language_id', 'type'=>'int'),
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

//columns to be renamed
$columns = array(
	array('table'=>'channels', 'before'=>'title_en', 'after'=>'title'),
	array('table'=>'links', 'before'=>'text', 'after'=>'title'),
	array('table'=>'pages', 'before'=>'isInstancePage', 'after'=>'is_hidden'),
	array('table'=>'pages', 'before'=>'name', 'after'=>'title'),
	array('table'=>'pages', 'before'=>'helpText', 'after'=>'description')
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


//fix table data

//pages url should only be page name
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

echo '</ul>';

?>