<?php
include("../include.php");
$staff = db_query("SELECT homeemail FROM intranet_users WHERE isActive = 1 and homeemail is not null ORDER BY lastname");
while ($s = db_fetch($staff)) echo $s["homeemail"] . "<br>";
?>