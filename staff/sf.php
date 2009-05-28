<?php
include("../include.php");

$staff = db_query("SELECT email FROM users WHERE organization_id = 10 AND is_active = 1 ORDER BY lastname");
while ($s = db_fetch($staff)) {
	echo $s["email"] . "<br>";
}

?>