<?
include('include.php');

if ($posting) {
	$id = db_save('cal_events');
	if (getOption('channels')) db_checkboxes('channels', 'cal_events_to_channels', 'event_id', 'channel_id', $id);
	url_change('./event.php?id=' . $_GET['id']);
}


$e = db_grab('SELECT MONTH(e.start_date) month, YEAR(e.start_date) year FROM cal_events e WHERE e.id = ' . $_GET['id']);
	
drawTop();
echo drawNavigationCal($e['month'], $e['year'], true);

echo drawEventForm();

drawBottom();?>