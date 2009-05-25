<?	include("include.php");

if (!isset($_GET["id"])) $_GET["id"] = 1;

drawTop();
$locations = db_query("SELECT 
		o.id, 
		o.name
	FROM offices o 
	WHERE (SELECT COUNT(*) FROM users u WHERE u.officeID = o.id AND u.is_active = 1) > 0
	ORDER BY (SELECT COUNT(*) FROM users u WHERE u.officeID = o.id) DESC");
if (db_found($locations)) {
	$pages = array();
	while ($l = db_fetch($locations)) {
		$pages["/staff/locations.php?id=" . $l["id"]] = $l["name"];
	}
	if (count($pages) > 5) {
		array_splice($pages, 4);
		$pages["/staff/locations.php?id=other"] = "Other";
		
	}
	echo drawNavigationRow($pages, $location, true);
}

if ($_GET["id"] == "other") {
	echo drawStaffList("u.is_active = 1 AND u.officeID <> 1 AND u.officeID <> 6 AND u.officeID <> 11 AND u.officeID <> 9");
} else {
	echo drawStaffList("u.is_active = 1 and u.officeID = " . $_GET["id"]);
}

drawBottom();?>