<?php
include("../include.php");

$staff = db_query("SELECT email FROM users WHERE corporationID = 10 AND isActive = 1 ORDER BY lastname");
while ($s = db_fetch($staff)) {
	echo $s["email"] . "<br>";
}

?>