<?
include("../include.php");

if ($posting) {
	$id = db_enter("queries", "databaseID name description query");
	$db = db_grab("SELECT dbname FROM queries_databases WHERE id = " . $_POST["databaseID"]);
	db_switch($db);
	$_POST["query"] = str_replace("'", "", $_POST["query"]); //undo what db_enter just did
	if (db_query($_POST["query"], 1, true)) {
		url_change("./");
	} else {
		url_change("./query_edit.php?id=" . $id, true);
	}
}
	
drawTop();

if (isset($_GET["id"])) {
	$r = db_grab("SELECT 
			q.databaseID,
			d.dbname,
			q.name,
			q.description,
			q.query,
			q.isActive
		FROM queries q 
		JOIN queries_databases d ON d.id = q.databaseID
		WHERE q.id = " . $_GET["id"]);
	/*db_switch($r["dbname"]);
	db_query($r["query"], false, true);
	db_switch($_josh["db"]["database"]);*/
} else {
	$r["isActive"] = 1;
}

$form = new intranet_form;
$form->addRow("hidden", "", "isActive", $r["isActive"]);
$form->addRow("select", "Database", "databaseID", "SELECT id, dbname from queries_databases order by dbname", @$r["databaseID"], true);
$form->addRow("itext", "Name", "name", @$r["name"], "", false, 50);
$form->addRow("textarea", "Description", "description", @$r["description"]);
$form->addRow("textarea-plain", "Query", "query", @$r["query"]);
$form->addRow("submit",   "Save Changes");
if (isset($_GET["id"])) {
	$form->draw("<a href='/queries/'>Database Queries</a> &gt; Edit Query");
} else {
	$form->draw("Add New Query");
}

drawBottom() ?>

