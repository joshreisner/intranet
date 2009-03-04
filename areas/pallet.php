<?
$left = true;
foreach ($areas as $a) {
	if (!$modules[$a]["isPublic"] && !$modules[$a]["isAdmin"]) continue;
	if ($left) echo "<tr>";
	echo '<td width="50%"><a href="' . $modules[$a]["url"] . '">' . $modules[$a]["name"] . '</a></td>';
	if (!$left) echo "</tr>";
	$left = ($left) ? false : true;
}
if (!$left) echo '<td width="50%"></td></tr>';