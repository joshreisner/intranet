<?php
$orgs = db_query("SELECT id, title FROM external_orgs_types ORDER BY title");
$types = array();
while ($o = db_fetch($orgs)) {
	$types[] = '<a href="/' . $m["folder"] . '/?type=' . $o["id"] . '">' . format_string($o["title"], 22) . '</a>';
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