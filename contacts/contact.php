<?php
include('../include.php');

url_query_require();
echo drawTop();

$r = db_grab('SELECT
		(SELECT t.tag FROM contacts_tags t JOIN contacts_to_tags c2t ON t.id = c2t.tag_id WHERE t.is_active = 1 AND t.type_id = 10 AND c2t.contact_id = c.id) salutation,
		c.firstname,
		c.lastname,
		(SELECT t.tag FROM contacts_tags t JOIN contacts_to_tags c2t ON t.id = c2t.tag_id WHERE t.is_active = 1 AND t.type_id = 11 AND c2t.contact_id = c.id) suffix,
		c.organization,
		c.title,
		c.address_1,
		c.address_2,
		RIGHT("00000" + RTRIM(c.zip), 5) zip,
		c.phone,
		c.fax,
		c.mobile_phone,
		c.email,
		z.city,
		z.state,
		c.notes
	FROM contacts c
	LEFT JOIN zip_codes z ON c.zip = z.zip
	WHERE c.id = ' . $_GET['id']);

$d = new display();
$d->row('Name', $r['salutation'] . ' ' . $r['firstname'] . ' ' . $r['lastname'] . ($r['suffix'] ? ', ' . $r['suffix'] : ''));
$d->row('Company', $r['organization']);
$d->row('Job Title', $r['title']);
$d->row('Address', $r['address_1'] . ($r['address_2'] ? BR . $r['address_2'] : false) . BR . $r['city'] . ', ' . $r['state'] . ' ' . $r['zip']);
$d->row('Phone', $r['phone']);
$d->row('Fax', $r['fax']);
$d->row('Mobile', $r['mobile_phone']);
$d->row('Email', draw_link('mailto:' . $r['email']));
$d->row('Notes', nl2br($r['notes']));
echo $d->draw();

$result = db_table('SELECT t.id, t.tag, y.title "group" FROM contacts_to_tags c2t JOIN contacts_tags t ON c2t.tag_id = t.id JOIN contacts_tags_types y ON t.type_id = y.id WHERE c2t.contact_id = ' . $_GET['id']);
$t = new table('contacts_tags');
$t->set_column('tag');
foreach ($result as &$r) {
	$r['tag'] = draw_link('value.php?id=' . $r['id'], $r['tag']);
}
echo $t->draw($result);
echo drawBottom();
?>