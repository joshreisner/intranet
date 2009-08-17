<?php
include('include.php');

$users = db_table('SELECT id, ' . db_updated() . ' FROM users');

foreach ($users as $u) {
	echo draw_img(file_dynamic('users', 'image_large', $u['id'], 'jpg', $u['updated']));
	echo draw_img(file_dynamic('users', 'image_medium', $u['id'], 'jpg', $u['updated']));
	echo draw_img(file_dynamic('users', 'image_small', $u['id'], 'jpg', $u['updated']));
}

?>