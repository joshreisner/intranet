<?
include("include.php");

//autocorrect
$target		= false;
$req		= (isset($_SERVER["REDIRECT_URL"])) ? strToLower($_SERVER["REDIRECT_URL"]) : false;
$referrer	= (isset($_SERVER["HTTP_REFERER"])) ? strToLower($_SERVER["HTTP_REFERER"]) : false;

if (!$req) {
	//var_dump($_SERVER);
	$queryParts = explode($_josh["request"]["host"], $_SERVER["QUERY_STRING"]);
	if (count($queryParts) > 1) $req = substr($queryParts[1], 3);
}

if (stristr($req, "/it")) {
	$target = str_replace("/it", "/helpdesk", $req);
} elseif (stristr($req, "/bulletin_board")) {
	$target = str_replace("/bulletin_board", "/bb", $req);
} elseif (stristr($req, "/calendar")) {
	$target = str_replace("/calendar", "/cal", $req);
} elseif (stristr($req, "/staff/comings.php") || stristr($req, "/staff/goings.php")) {
	$target = "/staff/changes.php";
} elseif (stristr($req, "/departments/administration")) {
	$target = str_replace("/departments/administration", "/openings", $req);
} elseif (stristr($req, "/departments/earnfair")) {
	$target = str_replace("/departments/earnfair", "/queries", $req);
} elseif (stristr($req, "/departments/resource_development")) {
	$target = str_replace("/departments/resource_development", "/funders", $req);
} elseif (stristr($req, "/docs")) {
	$target = str_replace("/docs", "/docs", $req);
} elseif (stristr($req, "/btw")) {
	//back to work application ~ used to have the intranet domain
	$target = "http://btw.seedco.org" . $req;
} elseif (stristr($req, "msoffice/cltreq.asp")) {
	//m$ft internet explorer discussion bar, no redirect
} elseif (stristr($req, "favicon.ico")) {
	//site favorite icon, no redirect
} elseif (stristr($req, "_vti_")) {
	//looking for m$ft front page extensions, no redirect
} elseif ($_SESSION["user_id"] != 1) {
	//user is admin, send email
	$msg = $_SESSION["full_name"] . " couldn't find " . url_base() . $req;
	if ($referrer) $msg .= "<br><br>Referred by " . $referrer;
}

if ($target) url_change($target, true);

echo drawTop();

echo drawMessage("<b>Error: Page Not Found</b><br>
Sorry, the page you're looking for isn't here!  If you feel you reached this page in error, please contact 
<a href='mailto:josh@joshreisner.com'>Josh Reisner</a> so it can be fixed.");

echo drawBottom();?>