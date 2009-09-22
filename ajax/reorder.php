<?php
include('../include.php');
$array = array_ajax();

db_query('UPDATE ' . $array['update'] . ' SET precedence = NULL');

//email('josh@joshreisner.com', draw_array($array));

foreach ($array as $key=>$value) {
	$key = urldecode($key);
	if (format_text_starts($array['key'], $key)) db_query('UPDATE ' . $array['update'] . ' SET precedence = ' . (format_numeric($key, true) + 1) . ' WHERE id = ' . $value);
}

refreshObjectCount($array['update']);

echo 'Updated ' . $array['update'];
?>