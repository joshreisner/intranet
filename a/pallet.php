<?php
$array = array();
foreach($modulettes as $o) {
	if (!$_SESSION["is_admin"] && !$o["is_public"] && !$o["is_admin"]) continue;
	$array[] = draw_link('/' . $m['folder'] . '/' . $o["folder"] . '/', $o['title']);
}
$return .= draw_table_rows($array);
?>