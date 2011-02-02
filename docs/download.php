<?php
include('../include.php');

url_query_require();

$d = db_grab('SELECT 
		d.title, 
		t.extension, 
		d.content 
	FROM docs d 
	JOIN docs_types t ON d.type_id = t.id
	WHERE d.id = ' . $_GET['id']);

db_query('INSERT INTO docs_views ( documentID, userID, viewedOn ) VALUES ( ' . $_GET['id'] . ', ' . user() . ', ' . db_date() . ' )');

file_download($d['content'], $d['title'], $d['extension'])
?>