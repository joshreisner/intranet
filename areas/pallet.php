<?
$left = true;
foreach ($areas as $a) {
	if (!$_SESSION["is_admin"] && !$modules[$a]["isPublic"] && !$modules[$a]["is_admin"]) continue;
	if ($left) echo "<tr>";
	echo '<td width="50%"><a href="' . $modules[$a]["url"] . '">' . $modules[$a]["title"] . '</a></td>';
	if (!$left) echo "</tr>";
	$left = ($left) ? false : true;
}
if (!$left) echo '<td width="50%"></td></tr>';