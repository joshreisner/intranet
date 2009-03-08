<?
include("../include.php");
db_query("UPDATE users SET password = PWDENCRYPT('') WHERE user_id = " . $_GET["id"]);
url_change("view.php?id=" . $_GET["id"]);
?>