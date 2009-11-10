<?php
include('../../include.php');

echo drawTop();

echo drawStaffList('u.is_active = 1 AND (SELECT COUNT(*) FROM users_to_modules a WHERE a.user_id = u.id) > 0', 'There are no administrators in this site, which is a problem.');

echo drawBottom();

?>