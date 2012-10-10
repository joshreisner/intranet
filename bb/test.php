<?php
include('include.php');

//update 
db_query('UPDATE bb_topics t SET t.replies = (SELECT COUNT(*) FROM bb_followups f WHERE f.topic_id = t.id and f.is_active = 1)');

/*deleting all followups?  not sure why
$followups = db_table('SELECT f.topic_id, f.id FROM bb_followups f JOIN bb_topics t ON f.topic_id = t.id WHERE t.is_admin');
foreach ($followups as $f) {
	db_delete('bb_followups', $f['id']);
	db_query('UPDATE bb_topics SET replies = 0 WHERE id = ' . $f['topic_id']);
}*/

echo 'done';

