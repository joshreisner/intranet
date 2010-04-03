<?php
include('../include.php');

echo drawTop();

if (url_action('delete')) {
	db_delete('external_orgs');
	url_change('./');
}

if (url_id()) {	
	echo drawTableStart();
	echo drawHeaderRow($page['breadcrumbs'] . $page['title'], 2, getString('edit'), 'edit/?id=' . $_GET['id'], getString('delete'), drawDeleteLink());

	$r = db_grab('SELECT e.title' . langExt() . ' title, e.url, e.description' . langExt() . ' description FROM external_orgs e WHERE e.id = ' . $_GET['id']);
?>
	<tr>
		<td class="left"><?=getString('title')?></td>
		<td class='title'><?=draw_link($r['url'], $r['title'])?></td>
	</tr>
	<tr>
		<td class="left"><?=getString('description')?></td>
		<td class="text"><?=$r['description']?></td>
	</tr>
	<? if (getOption('channels')) {?>
	<tr>
		<td class="left"><?=getString('channels_label')?></td>
		<td>
			<? $channels = db_query('SELECT
				c.title' . langExt() . ' title
			FROM external_orgs_to_channels e2c
			JOIN channels c ON e2c.channel_id = c.id
			WHERE e2c.org_id = ' . $_GET['id']);
				while ($c = db_fetch($channels)) {?>
				 &#183; <?=$c['title']?></a><br>
			<? }?>
		</td>
	</tr>
	<? }?>	
</table>
<? } else {
	//main table
	$result = db_table('SELECT 
		e.id, e.title' . langExt() . ' title, t.title' . langExt() . ' "group", e2t.type_id
		FROM external_orgs e
		JOIN external_orgs_to_types e2t ON e.id = e2t.org_id
		JOIN external_orgs_types t ON e2t.type_id = t.id
		WHERE e.is_active = 1 ORDER BY t.title, e.title
		');
	$t = new table('external_orgs_types', drawHeader(array('#bottom'=>getString('add_new'))));
	$t->col('title', 'l', getString('title'));
	foreach ($result as &$r) {
		$r['group'] = draw_link('./type.php?id=' . $r['type_id'], $r['group']);
		$r['title'] = draw_link('./?id=' . $r['id'], $r['title']);
	}
	echo $t->draw($result, 'There are no external orgs added yet.');
	
	//add new
	include('edit.php');
}

echo drawBottom();
?>