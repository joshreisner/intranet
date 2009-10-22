<?
$left = true;

foreach($modulettes as $m) {
	if (!$_SESSION["is_admin"] && !$m["is_public"] && !$m["is_admin"]) continue;
	if ($left) echo "<tr>";
	echo '<td width="50%"><a href="/' . $m["folder"] . '/">' . $m["title"] . '</a></td>';
	if (!$left) echo "</tr>";
	$left = ($left) ? false : true;
}
if (!$left) echo '<td width="50%"></td></tr>';