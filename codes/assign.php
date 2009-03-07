<?
include("../include.php");
drawTop();
$result = db_query("SELECT code FROM ldcodes");
while ($r = db_fetch($result)) {
	$codes[] = $r["code"];
}
$result = db_query("SELECT userID, firstname, lastname FROM users WHERE isactive = 1 AND officeID = 1");
$counter = 0;
while ($r = db_fetch($result)) {
	db_query("UPDATE users SET longdistancecode = " . $codes[$counter] . " WHERE userID = " . $r["userID"]);
	$counter++;
}
drawBottom();
?>