<?php
//$_josh["debug"] = true;

$_SERVER["DOCUMENT_ROOT"]	= "D:\Sites\seedco.org\intranet\\";
$_SERVER["HTTP_HOST"]		= "intranet.seedco.org";
$_SERVER["SCRIPT_NAME"]		= "/helpdesk/alerts.php";
$_COOKIE["last_login"]		= "jreisner@seedco.org";

chdir(dirname(__FILE__));

include("include.php");

$tickets = db_query("SELECT id FROM helpdesk_tickets WHERE priorityID = 1 AND statusID <> 9 AND DATEDIFF(mi, createdOn, GETDATE()) > 60");
while ($t = db_fetch($tickets)) emailITTicket($t["id"], "critical", true);

$tickets = db_query("SELECT id FROM helpdesk_tickets WHERE statusID <> 9 AND DATEDIFF(dd, createdOn, GETDATE()) > 5");
//while ($t = db_fetch($tickets)) emailITTicket($t["id"], "languishing", true);

echo "finished!";

?>