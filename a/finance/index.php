<? include("../../include.php");

drawTop();

if ($_josh["db"]["language"] == "mssql") {
	db_switch("trackit");
	$l = db_grab("SELECT MAX(loadDate) loadDate FROM _josh_loads");
	echo drawMessage("These database indexes were loaded: " . format_date($l["loadDate"], true, " at "));
	db_switch($_josh["db"]["database"]);
}
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Reports", 1);?>
	<tr><td><a href="totals.php">Totals</a></td></tr>
	<tr><td><a href="percentages.php">Percentages (without Vacation)</a></td></tr>
</table>
<? drawBottom();?>