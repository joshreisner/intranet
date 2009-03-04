<?php
include("../include.php");

function drawNavigationCal($month, $year, $linked=false) {
	global $_josh;

	$return = '
	<table class="navigation cal" cellspacing="1">
		<tr class="cal-hilite">
			<td width="12.5%"><a href="./?month=12&year=' . ($year - 1) . '">&lt; ' . ($year - 1) . '</a></td>';
	for ($i = 1; $i < 13; $i++) {
		$return .= '<td width="6.25%"';
		if ($month == $i) $return .= ' class="selected"';
		$return .= '>';
		if (($month != $i) || $linked) $return .= '<a href="./?month=' . $i . '&year=' . $year . '">';
		$return .= $_josh["mos"][$i - 1];
		if (($month != $i) || $linked) $return .= '</a>';
		$return .= '</td>';
	}
	$return .= '
			<td width="12.5%"><a href="./?month=1&year=' . ($year + 1) . '">' . ($year + 1) . ' &gt;</a></td>
		</tr>
	</table>';
	return $return;
}
?>