<?php
include('../../include.php');
echo drawtop();

$t = new table('ldcodes', drawHeader(false, 'Long Distance Codes'));
$t->set_column('code', 'c', 'Code');
$t->set_column('user', 'l', 'User');
$result = db_table('SELECT l.code, (SELECT CONCAT_WS(",", u.lastname, u.firstname, u.id) FROM users u WHERE u.longDistanceCode = l.code AND u.is_active = 1) user FROM ldcodes l ORDER BY user');
foreach ($result as &$r) {
	if ($r['user']) {
		$r['group'] = 'Assigned';
		list($lastname, $firstname, $id) = explode(',', $r['user']);
		$r['user'] = draw_link('/staff/view.php?id=' . $id, $lastname . ', ' . $firstname);
	} else {
		$r['group'] = 'Unassigned Codes';
	}
}
echo $t->draw($result, 'There are no long distance codes');
echo drawBottom();
?>