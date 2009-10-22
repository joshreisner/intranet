<?
include("../include.php");

$r = db_grab("SELECT 
				q.query, 
				q.name,
				d.dbname
			FROM queries q
			JOIN queries_databases d ON q.databaseID = d.id
			WHERE q.id = " . $_GET["id"]);

db_switch($r["dbname"]);

if (!$result = db_query($r["query"], false, true)) {
	url_change("./query_edit.php?id=" . $_GET["id"], true);
}

$filename		= $r["name"];
$num_columns	= db_num_fields($result);
$num_rows		= 0;
$fields			= array();

//get header row
for ($i = 0; $i < $num_columns; $i++) {
	$name = db_fetch_field($result, $i); 
	array_push($fields, $name->name . "|||" . db_field_type($result, $i));
}

$return = '
<table border="1">
	<tr bgcolor="#fffceo">';
	foreach ($fields as $field) { 
		list($name, $datatype) = explode("|||", $field);
		$return .= '
		<td><b>' . trim(str_replace("_", " ", $name)) . '</b></td>
		';
		}
	$return .= '</tr>';

while ($r = db_fetch($result)) {
	$return .= '<tr>';
	reset($fields);
	foreach ($fields as $field) {
		list($name, $datatype) = explode("|||", $field);
		if ($datatype == "datetime") $r[$name] = format_date_excel($r[$name]);
			$return .= '<td>' . $r[$name] . '</td>';
		}
	$return .= '</tr>';
	$num_rows++;
}

$return .= '</table>';
//save exec info
	db_switch($_josh["db"]["database"]);	
	db_query("INSERT INTO queries_executions ( 
				queryID, 
				userID, 
				executedOn, 
				num_rows, 
				num_columns
			) VALUES (
				{$_GET["id"]},
				{$_SESSION["user_id"]},
				GETDATE(),
				{$num_rows},
				{$num_columns}
			)");

file_download($return, $filename, "xls");
?>