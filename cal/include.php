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

function drawEventForm() {
	global $page, $page['is_admin'];
	$f = new form('cal_events', @$_GET['id'], $page['name']);
	if (url_id()) $f->set_title_prefix(drawHeader(false, ' '));
	if ($page['is_admin']) $f->set_field(array('name'=>'created_user', 'class'=>'admin', 'type'=>'select', 'sql'=>'SELECT id, CONCAT_WS(", ", lastname, firstname) FROM users WHERE is_active = 1 ORDER BY lastname, firstname', 'default'=>$_SESSION['user_id'], 'required'=>true, 'label'=>'Posted By'));
	$f->set_field(array('name'=>'type_id', 'type'=>'select', 'sql'=>'SELECT id, description FROM cal_events_types ORDER BY description', 'label'=>'Category'));
	if (getOption('channels')) $f->set_field(array('name'=>'channels', 'type'=>'checkboxes', 'label'=>'Networks', 'options_table'=>'channels', 'linking_table'=>'cal_events_to_channels', 'object_id'=>'event_id', 'option_id'=>'channel_id'));
	$f->set_order('created_user,title, start_date, end_date, type_id, description, channels');
	return $f->draw();
}
?>