<?php
include('include.php');

$orgs = array();
if (getOption('staff_allowshared')) {
	$orgs[0] = 'Shared';
}
$orgs = db_table('SELECT id, title from organizations WHERE is_active = 1 ORDER BY title', $orgs, false, false);

echo drawTop();

if (count($orgs) < 8) {
?>
<table class='navigation staff' cellspacing='1'>
	<tr class='staff-hilite'>
		<? foreach ($orgs as $o) { ?>
		<td width='14.28%'<? if (url_id() == $o['id']) {?> class='selected'<? }?>><? if (url_id() != $o['id']) {?><a href='organizations.php?id=<?=$o['id']?>'><? } else { ?><b><? }?><?=format_string($o['title'], 26)?></b></a></td>
		<? }?>
	</tr>
</table>
<?
} else {
	echo drawPanel(draw_form_select('foo', $orgs, url_id(), false, false, 'location.href=\'' . $request['path'] . '?id=\' + this.value'));
}

if (url_id()) {
	$where = ($_GET['id'] == 0) ? ' IS NULL ' : ' = ' . $_GET['id'];
	echo drawStaffList('u.is_active = 1 AND u.organization_id ' . $where, 'This organization has no staff associated with it.', array('add_edit.php'=>getString('add_new')), draw_link($request['path_query'], $page['title']) . ' &gt; ' . db_grab('SELECT title FROM organizations WHERE id = ' . $_GET['id']));
} else {
	$t = new table('foo', drawHeader());
	$t->col('title', false, getString('title'));
	foreach ($orgs as &$o) $o['title'] = draw_link('organizations.php?id=' . $o['id'], $o['title']);
	echo $t->draw($orgs);
}

echo drawBottom();
?>