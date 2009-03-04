<?php
$pageIsPublic = true;
include("include.php");
$redirect = false;
if (isset($_GET["logout"])) {
	error_debug("<b>index.php</b> Logging Out");
	cookie("last_login");
	$redirect = "/";
} elseif (login(@$_COOKIE["last_login"], "", true)) { //log in with last login
	error_debug("<b>index.php</b> Cookie Found (good)");
	$redirect = (!empty($_GET["goto"])) ? $_GET["goto"] : $_SESSION["homepage"];
} elseif ($posting) { //logging in
	error_debug("<b>index.php</b> Posting");
	if (login($_POST["email"], $_POST["password"])) {
		error_debug("<b>index.php</b> Login successful");
		cookie("last_login", $_POST["email"]);
		$redirect = (!empty($_POST["goto"])) ? $_POST["goto"] : $_SESSION["homepage"];
   	} else {
		error_debug("<b>index.php</b> Login unsuccessful");
		$redirect = "/";
    }
}
if ($redirect) url_change($redirect);
include($_josh["root"] . $locale . "login.php");
?>