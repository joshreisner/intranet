<?php
if (!empty($helpdeskStatus)) {
	echo "<tr class='admin' style='background-color:#fffce0; font-weight:bold;' align='center'><td style='padding:8px;' colspan='2'>" . $helpdeskStatus . "</td></tr>";
}
$items = array();
foreach ($helpdeskOptions as $option) {
	$item[] = '<a href="/helpdesk/?dept=' . $option["id"] . '">' . $option["name"] . '</a> (' . format_num($option["num_open"], 0, true, 0) . ')';
}
?>
<tr>
	<td width="50%"><?=$item[0]?></td>
	<td width="50%"><?=$item[1]?></td>
</tr>
<tr>
	<td width="50%"><?=$item[2]?></td>
	<td width="50%"><?=$item[3]?></td>
</tr>