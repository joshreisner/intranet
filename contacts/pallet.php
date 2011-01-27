<?
$return .= '
<tr height="20">
	<td width="50%">' . draw_link('/'. $m['folder'] . '/', 'Search') . '</td>
	<td width="50%">' . draw_link('/'. $m['folder'] . '/tags.php', 'Tags') . '</td>
</tr>
<tr height="20">
	<td width="50%">' . draw_link('/'. $m['folder'] . '/contacts.php', 'Alphabetical List') . '</td>
	<td width="50%">' . draw_link('/'. $m['folder'] . '/activity.php', 'Activity') . '</td>
</tr>';
?>