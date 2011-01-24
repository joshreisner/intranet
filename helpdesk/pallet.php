<?php
if (!empty($helpdeskStatus)) {
	$return .= '<tr class="admin" style="background-color:#fffce0; font-weight:bold;" align="center"><td style="padding:8px;" colspan="2">' . $helpdeskStatus . '</td></tr>';
}
if (!empty($helpdeskOptions)) {
	$items = array();
	foreach ($helpdeskOptions as $option) {
		$items[] = draw_link('/helpdesk/?dept=' . $option['id'], $option['name']) . ' (' . format_num($option["num_open"], 0, true, 0) . ')';
	}
	$return .= draw_table_rows($items);
}
?>