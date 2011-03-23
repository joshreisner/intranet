<?php
include('include.php');

echo drawTop();

$fields = array('u.lastname', 'u.firstname', 'u.nickname', 'u.title', 'u.email', 'departmentName');
$terms = explode(' ', format_quotes($_GET['q']));
$where = array();
foreach ($terms as $t) {
	if (!empty($t)) foreach ($fields as $f) $where[] = $f . ' LIKE "%' . $t . '%"';
}
$links = ($page['is_admin']) ? array('add_edit.php'=>getString('add_new')) : false;
echo drawStaffList('u.is_active = 1 and (' . implode(' OR ', $where) . ')', getString('staff_search_empty'), $links, false, $terms);

echo drawBottom();
?>