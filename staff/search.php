<? include("include.php");

echo drawTop();

$fields = array("u.lastname", "u.firstname", "u.nickname", "u.title", "departmentName");
$terms = explode(" ", str_replace("'", "''", $_GET["q"]));
$where = array();
foreach ($terms as $t) {
	if (!empty($t)) {
		foreach ($fields as $f) $where[] = $f . " LIKE '%" . $t . "%'";
	}
}
echo drawStaffList("u.is_active = 1 and (" . implode(" OR ", $where) . ")", $terms);
echo drawBottom();
?>