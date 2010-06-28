<?php
include('../include.php');

$emails = array('josh@joshreisner.com', 'foo [at] bar', 'test@mweb.co.za;testest@clara.net');
$emails = db_array('SELECT email FROM users WHERE is_active = 1 ORDER BY email');

$good = $bad = array();
foreach ($emails as $e) {
	if (!$good[] = format_email($e)) {
		array_pop($good);
		$bad[] = $e;
	}
}

echo 'good emails:' . draw_list($good);

echo '<hr>bad emails:' . draw_list($bad);

//email($emails, 'this is some test content', 'this is a test');
?>