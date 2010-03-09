<?
$pageIsPublic = true;
include("../include.php");

echo drawTopSimple(getString('password_reset'));

echo drawMessage(getString('password_confirm'));

echo drawBottomSimple();
?>