<?php
include("../../include.php");

echo drawTop();

$t = new table('emails', drawHeader());
$t->set_column('address', 'l', 'Email Address');
$t->set_column('created_date', 'r', 'Sent');
$emails = db_table('SELECT address, subject "group", created_date FROM emails ORDER BY created_date DESC', 100);
foreach ($emails as &$e) {
	$e['created_date'] = format_date_time($e['created_date']);
}
echo $t->draw($emails);


echo drawBottom();
?>