<?php
include('../include.php');

//need a type
url_query_require('./');
echo drawTop();

if (url_action('delete')) {
	db_delete('external_orgs', $_GET['org_id']);
	url_drop('action,org_id');
}

//main table
echo drawTableStart();
if ($page['is_admin']) {
	echo drawHeaderRow(db_grab('SELECT title FROM external_orgs_types WHERE id = ' . $_GET['id']), 1, 'add new', '#bottom');
} else {
	echo drawHeaderRow();
}
$orgs = db_query('SELECT 
	o.id, o.url, o.title, o.description 
	FROM external_orgs o 
		' . getChannelsWhere('external_orgs', 'o', 'org_id') . '
		AND (SELECT COUNT(*) FROM external_orgs_to_types t WHERE t.org_id = o.id AND t.type_id = ' . $_GET['id'] . ' > 0)
	ORDER BY o.title');
if (db_found($orgs)) {
	while ($o = db_fetch($orgs)) {?>
	<tr>
		<td class='text'>
			<? if ($page['is_admin']) {?>
			<a href='<?=drawDeleteLink('delete this org?', $o['id'], 'delete', 'org_id')?>' class='button-light right'>del</a>
			<a href='edit.php?id=<?=$o['id']?>' class='button-light right'>edit</a>
			<? }?>
			<a class='title' href='<?=$o['url']?>'><?=$o['title']?></a><br><?=$o['description']?>
		</td>
	</tr>
	<? }
} else {
	echo drawEmptyResult('There are no orgs listed for this type.');
}
echo drawTableEnd();

//add new
include('edit.php');
echo drawBottom();?>