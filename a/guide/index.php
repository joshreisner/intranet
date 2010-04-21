<?php
include('../../include.php');

echo drawTop();

echo drawTableStart();
echo drawHeaderRow(false, 1, getString('edit'), 'edit/');
echo '<tr><td class="text">' . db_grab('SELECT content' . langExt() . ' FROM guide') . '</td></tr>';
echo drawTableEnd();


echo drawBottom();
?>