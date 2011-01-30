<?php
include("../include.php");
echo drawTop();

$t = new table('bb_topics', drawHeader());
$t->set_column('contact', 'l', 'Contact Record');
$t->set_column('action');
//$t->set_column('contact', 'l', 'Done By');
$t->set_column('when', 'r');

$result = db_table('SELECT
			c.id,
			c.firstname,
			c.lastname,
			c.updated_date,
			' . db_updated('c') . '
		FROM contacts c
		ORDER BY updated DESC', 40);
foreach ($result as &$r) {
	$r['contact'] = draw_link('contact.php?id=' . $r['id'], $r['lastname'] . ', ' . $r['firstname']);
	$r['action'] = ($r['updated_date']) ? 'Update' : 'New Contact';
	$r['when'] = format_date($r['updated']);
}
echo $t->draw($result);

echo drawBottom();
?>