<?php
$pageIsPublic = true;
include('include.php');

echo '<h1>Convert Character Sets</h1>';

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