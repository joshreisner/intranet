<?
$left = true;

foreach($modulettes as $o) {
	if (!$_SESSION["is_admin"] && !$o["is_public"] && !$o["is_admin"]) continue;
	if ($left) $return .= "<tr>";
	$return .= '<td width="50%"><a href="/' . $m['folder'] . '/' . $o["folder"] . '/">' . $o["title"] . '</a></td>';
	if (!$left) $return .= "</tr>";
	$left = ($left) ? false : true;
}
if (!$left) $return .= '<td width="50%"></td></tr>';