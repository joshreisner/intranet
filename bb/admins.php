<?php
include('../include.php');
echo drawTop();

$t = new table('administrators', drawHeader());
$t->col('picture', 'l', '&nbsp;', '50');
$t->col('name');
$t->col('organization');
$t->col('last_login', 'r');

$result = db_table('SELECT u.id, u.firstname, u.lastname, u.title, o.title organization, u.organization_id, u.lastLogin last_login FROM users u LEFT JOIN organizations o ON u.organization_id = o.id JOIN users_to_modules u2m ON u.id = u2m.user_id WHERE u.is_active = 1 AND u2m.module_id = ' . $page['module_id'] . ' ORDER BY u.lastname, u.firstname');

foreach ($result as &$r) {
	$link = '/staff/view.php?id=' . $r['id'];
	$r['picture'] = draw_img(file_dynamic('users', 'image_small', $r['id'], 'jpg'), $link);
	$r['name'] = draw_link($link, $r['firstname'] . ' ' . $r['lastname']);
	if ($r['organization']) $r['organization'] = draw_link('/staff/organizations.php?id=' . $r['organization_id'], $r['organization']) . '<br>';
	$r['organization'] .= $r['title'];
	$r['last_login'] = format_date($r['last_login']);
}

echo $t->draw($result, 'No administrators for this module yet!');

echo drawBottom();
?>