<?php
include('../include.php');

$result = db_table('SELECT
	u.id link,
	u.firstname,
	u.lastname,
	u.title,
	d.departmentName department,
	o.title organization,
	u.email,
	(SELECT CASE WHEN u.rankID = 9 THEN "No" ELSE "Yes" END) is_staff
	FROM users u
	LEFT JOIN departments d ON u.departmentID = d.departmentID
	JOIN organizations o ON u.organization_id = o.id
	WHERE u.is_active = 1
	ORDER BY u.lastname, u.firstname');

foreach ($result as &$r) $r['link'] = draw_link(url_base() . '/staff/view.php?id=' . $r['link'], $r['link']);

file_array($result, 'employees');