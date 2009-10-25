<?php include("../../include.php");

error_reporting(E_ALL);

if ($_POST) {
	db_switch("trackit");
	
	//establish vars
	//die($_POST["start"]);
	$projects						= array();
	list($startMonth, $startYear)	= explode("/", $_POST["start"]);
	list($endMonth, $endYear)		= explode("/", $_POST["end"]);
	$startdate						= $startMonth . "/1/" . $startYear;
	$enddate						= $endMonth   . "/28/" . $endYear;
	$timeframe						= "h.midDate > '" . $startdate . "' AND h.midDate < '" . $enddate . "'";
	$reportname						= "Percentages " . $_POST["start"];
	if ($_POST["start"] != $_POST["end"]) $reportname .= " to " . $_POST["end"];
	
	//start output file
	$file  = '<table border="1" style="font-family:verdana; font-size:10px;">
				<tr bgcolor="#dddddd">
					<td width="140" align="center" rowspan="2"><b>' . $reportname . '</b></td>';
	
	//load projects into array & finish first row
	$result = db_query("SELECT
			p.id,
			p.projectName,
			p.projectID,
			p.taskName, 
			p.taskID
		FROM _josh_projects p
		WHERE 
			p.isVacation = 0 AND
			(SELECT SUM(h.hrs) FROM _josh_hours h WHERE h.projectID = p.id AND $timeframe) IS NOT NULL
		ORDER BY projectName, taskName");
	while ($r = db_fetch($result)) {
		$projects[] = $r["id"];
		$tasks[] = $r["taskName"];
		$file .= '<td width="90" align="center" valign="bottom">' . $r["projectName"] . '</td>';
	}
	$file .= '<td width="60" align="right" rowspan="2"><b>Total</b></td></tr>';

	//second row: tasks
	$file .= '<tr bgcolor="#dddddd">';
	foreach ($tasks as $task) {
		$file .= '<td align="center" valign="bottom">' . $task . '</td>';
	}
	
	//start outer loop: employees
	$employees = db_query("SELECT 
			e.[id],
			e.[first],
			e.[last],
			(SELECT SUM(h.hrs) FROM _josh_hours h JOIN _josh_projects p ON h.projectID = p.id WHERE p.isVacation = 0 AND h.employeeID = e.id AND $timeframe) total
		FROM employee e 
		WHERE
			e.[last] IS NOT NULL AND
			(SELECT SUM(h.hrs) FROM _josh_hours h JOIN _josh_projects p ON h.projectID = p.id WHERE p.isVacation = 0 AND h.employeeID = e.id AND $timeframe) IS NOT NULL
		ORDER BY e.[last], e.[first], e.[id]");
	
	while ($e = db_fetch($employees)) {
		$file .= '<tr><td align="center"><nobr>' . $e["first"] . ' ' . $e["last"]    . '</nobr></td>';
		
		//load employee hours
		$totals = array();
		$hours = db_query("SELECT
					h.projectID,
					h.hrs
				FROM _josh_hours h
				JOIN _josh_projects p ON h.projectID = p.id
				WHERE p.isVacation = 0 AND h.employeeID = {$e["id"]} AND $timeframe");
		while($h = db_fetch($hours)) $totals[$h["projectID"]] = $h["hrs"];
		
		//inner loop: projects
		$counter = 0;
		foreach ($projects as $p) {
			$file .= '<td>';
			if (isset($totals[$p])) {
				$counter += round($totals[$p] / $e["total"] * 100, 2);
				$file .= round($totals[$p] / $e["total"] * 100, 2) . "%";
				//$total = round($rh["total"] / $rh["totaltotal"] * 100, 2);
			//} else {
				//$file .= "-";
			}
			$file .= '</td>';
		}
		$file .= '<td align="right">' . $counter . '</td></tr>';
	}
	$file .= '</table>';
	
	//die($file);
	file_download($file, $reportname, "xls");
}

echo drawTop();
?>
<table class="left" cellspacing="1">
	<?=drawHeaderRow("Percentages Report (without Vacation)", 2);?>
	<form method="post" action="<?=$_josh["request"]["path_query"]?>">
	<tr>
		<td class="left">Start Date</td>
		<td><?=draw_form_select_month("start", "1/2005", false, false, "field", false, true);?></td>
	</tr>
	<tr>
		<td class="left">End Date</nobr></td>
		<td><?=draw_form_select_month("end", "1/2005", false, false, "field", false, true);?></td>
	</tr>
	<tr>
		<td colspan="2" class="bottom"><?=draw_form_submit("run report")?></td>
	</tr>
	</form>
</table>	
<?=drawBottom();?>