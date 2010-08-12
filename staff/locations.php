<?php
include('include.php');

echo drawTop();

if (!isset($_GET['id'])) {
	$_GET['id'] = 1;
	$_josh['request']['path_query'] = '/staff/locations.php?id=1';
}

$locations = db_query('SELECT o.id, o.name' . langExt() . ' FROM offices o WHERE o.is_active = 1 AND (SELECT COUNT(*) FROM users u WHERE u.is_active = 1 AND u.officeID = o.id) > 0 ORDER BY o.precedence');
if (db_found($locations)) {
	$pages = array();
	$counter = 1;
	while ($l = db_fetch($locations)) {
		if ($counter < 6) {
			$pages['/staff/locations.php?id=' . $l['id']] = $l['name'];
		} else{
			if ($counter == 6) {
				$pages['/staff/locations.php?id=other'] = 'Other';
				$others = array();
			}
			$others[] = 'u.officeID <> ' . $l['id'];
		}
		$counter++;
	}
	echo drawNavigation($pages, 'path_query');
}

if ($_GET['id'] == 'other') {
	echo drawStaffList('u.is_active = 1 AND ' . implode($others, ' AND '));
} else {
	echo drawStaffList('u.is_active = 1 and u.officeID = ' . $_GET['id']);
}

echo drawBottom();
?>