<? include("../include.php");

$jobs = db_query("SELECT title, description FROM intranet_jobs order by created_date");

while ($j = db_fetch($jobs)) {
	echo "<h1>" . $j["title"] . "</h1>";
	echo $j["description"];
	echo "<hr>";
}

?>