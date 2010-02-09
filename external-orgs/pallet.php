<?php
$orgs = db_query('SELECT id, title' . langExt() . ' title FROM external_orgs_types ORDER BY title');
$types = array();
while ($o = db_fetch($orgs)) {
	$types[] = draw_link('/' . $m["folder"] . '/type.php?id=' . $o["id"], format_string($o["title"], 22));
}

$return .= '
	<tr>
		<td width="50%">' .@$types[0] . '</td>
		<td width="50%">' .@$types[1] . '</td>
	</tr>
	<tr>
		<td width="50%">' .@$types[2] . '</td>
		<td width="50%">' .@$types[3] . '</td>
	</tr>
	<tr>
		<td width="50%">' .@$types[4] . '</td>
		<td width="50%">' .@$types[5] . '</td>
	</tr>
	<tr>
		<td width="50%">' .@$types[6] . '</td>
		<td width="50%"></td>
	</tr>';