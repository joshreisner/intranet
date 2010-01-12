<?php
include('../include.php');

if (url_action('delete')) {
	if (!isset($_GET['delete_id']) && isset($_GET['id'])) $_GET['delete_id'] = $_GET['id'];
	$r = db_grab('SELECT firstname, lastname, endDate FROM users WHERE id = ' . $_GET['delete_id']);
	if ($r['endDate']) {
		db_query('UPDATE users SET is_active = 0, deleted_user = ' . $_SESSION['user_id'] . ', deleted_date = GETDATE() WHERE id = ' . $_GET['delete_id']);
	} else {
		db_query('UPDATE users SET is_active = 0, deleted_user = ' . $_SESSION['user_id'] . ', deleted_date = GETDATE(), endDate = GETDATE() WHERE id = ' . $_GET['delete_id']);
	}
	if (getOption('staff_alertdelete')) emailAdmins('<a href="' . url_base() . '/staff/view.php?id=' . $_GET['staffID'] . '">' . $r['firstname'] . ' ' . $r['lastname'] . '</a> was just deactivated on the Intranet.', 'Intranet: Staff Deleted');
	url_query_drop('action,delete_id');
}

function drawJumpToStaff($selectedID=false) {
	global $page;
	$nullable = ($selectedID === false);
	$return = drawPanel(getString('jump_to') . ' ' . drawSelectUser('', $selectedID, $nullable, 0, true, true, 'Staff Member:'));
	if ($page['is_admin']) { 
		if ($r = db_grab('SELECT COUNT(*) FROM users_requests')) {
			$return = drawMessage('There are pending <a href="requests.php">account requests</a> for you to review.') . $return;
		}
		
	}
	return $return;
}
?>