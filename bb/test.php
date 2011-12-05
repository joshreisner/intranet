<?php
include('include.php');

$followups = db_table('SELECT f.topic_id, f.id FROM bb_followups f JOIN bb_topics t ON f.topic_id = t.id WHERE t.is_admin');
foreach ($followups as $f) {
	db_delete('bb_followups', $f['id']);
	db_query('UPDATE bb_topics SET replies = 0 WHERE id = ' . $f['topic_id']);
}

echo 'done';

