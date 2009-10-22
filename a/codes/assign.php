<?
include("../include.php");
drawTop();
$result = db_query("SELECT code FROM ldcodes");
while ($r = db_fetch($result)) {
	$codes[] = $r["code"];
}
$result = db_query("SELECT user_id, firstname, lastname FROM users WHERE is_active = 1 AND officeID = 1");
$counter = 0;
while ($r = db_fetch($result)) {
	db_query("UPDATE users SET longdistancecode = " . $codes[$counter] . " WHERE user_id = " . $r["user_id"]);
	$counter++;
}
drawBottom();
?>