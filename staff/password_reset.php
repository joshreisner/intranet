<?
include("../include.php");
db_query("UPDATE intranet_users SET password = PWDENCRYPT('') WHERE userID = " . $_GET["id"]);
url_change("view.php?id=" . $_GET["id"]);
?>