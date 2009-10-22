<?
$left = true;

foreach($modulettes as $o) {
	if (!$_SESSION["is_admin"] && !$o["is_public"] && !$o["is_admin"]) continue;
	if ($left) echo "<tr>";
	echo '<td width="50%"><a href="/' . $m['folder'] . '/' . $o["folder"] . '/">' . $o["title"] . '</a></td>';
	if (!$left) echo "</tr>";
	$left = ($left) ? false : true;
}
if (!$left) echo '<td width="50%"></td></tr>';