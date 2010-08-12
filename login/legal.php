<?
$pageIsPublic = true;
include("../include.php");

echo drawSimpleTop(getString('legal_title'));

echo drawMessage(draw_container('h1', getString('legal_title')) . getString('legal_message'));

echo drawSimpleBottom();
?>