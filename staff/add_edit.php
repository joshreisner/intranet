<?php
include('../include.php');

echo drawTop();

$t = new form('users', @$_GET['id']);

$unset_fields = array('image_large', 'image_medium', 'image_small', 'password', 'lastLogin');
$t->unset_fields($unset_fields);
$t->set_field_labels(array('firstname'=>'First Name', 'lastname'=>'Last Name'));
$t->set_group('Public Information', 0);
echo $t->draw();

echo drawBottom();
?>