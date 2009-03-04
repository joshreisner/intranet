<?php
$oneFound = false;
if (isset($_SERVER['HTTP_COOKIE']) && !empty($_SERVER['HTTP_COOKIE'])) {
	$return = "You've got the following cookies: <ol>";
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    //die("cookie is ~" . $_SERVER['HTTP_COOKIE'] . "~");
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        setcookie($name, '', time()-1000, "/", $_SERVER["HTTP_HOST"]);
        setcookie($name, '', time()-1000, "/", str_replace("intranet", "", $_SERVER["HTTP_HOST"]));
        $return .= "<li>" . $name . "</li>";
    }
	$return .= "</ol><a href='./'>please refresh now</a>";
	echo $return;
} else {
	echo "You are all clear.  Go <a href='/'>Log in</a>.";
}

?>