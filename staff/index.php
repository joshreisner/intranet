<?php
include('include.php');

echo drawTop();

if (db_grab('SELECT COUNT(*) FROM users WHERE is_active = 1') > 70) {
	if (!isset($_GET['id'])) {
		$_josh['request']['path_query'] .= '?id=1';
		$_GET['id'] = 1;
	}
	
	echo drawNavigation(array('/staff/?id=1'=>'A - E', '/staff/?id=2'=>'F - J', '/staff/?id=3'=>'K - O', '/staff/?id=4'=>'P - T', '/staff/?id=5'=>'U - Z'));
	
	if ($_GET['id'] == 1) {
		$letters = ' AND (u.lastname LIKE "a%" OR u.lastname LIKE "b%" OR u.lastname LIKE "c%" OR u.lastname LIKE "d%" OR u.lastname LIKE "e%")';
	} elseif ($_GET['id'] == 2) {
		$letters = ' AND (u.lastname LIKE "f%" OR u.lastname LIKE "g%" OR u.lastname LIKE "h%" OR u.lastname LIKE "i%" OR u.lastname LIKE "j%")';
	} elseif ($_GET['id'] == 3) {
		$letters = ' AND (u.lastname LIKE "k%" OR u.lastname LIKE "l%" OR u.lastname LIKE "m%" OR u.lastname LIKE "n%" OR u.lastname LIKE "o%")';
	} elseif ($_GET['id'] == 4) {
		$letters = ' AND (u.lastname LIKE "p%" OR u.lastname LIKE "q%" OR u.lastname LIKE "r%" OR u.lastname LIKE "s%" OR u.lastname LIKE "t%")';
	} elseif ($_GET['id'] == 5) {
		$letters = ' AND (u.lastname LIKE "u%" OR u.lastname LIKE "v%" OR u.lastname LIKE "w%" OR u.lastname LIKE "x%" OR u.lastname LIKE "y%" OR u.lastname LIKE "z%")';
	}
}

echo drawJumpToStaff();

echo drawStaffList('u.is_active = 1' . @$letters, getString('staff_empty'), ($page['is_admin'] ? array('add_edit.php'=>getString('add_new')) : false));

echo drawBottom();
?>