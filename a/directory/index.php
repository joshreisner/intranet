<?php
include("../../include.php");
drawTop();

$t = new table();
$t->set_column("organization");
$t->set_column("location");
$t->set_column("updated", "r");
$t->set_title(drawHeader());

$result = db_table("SELECT o.id, o.name organization, z.city, z.state, o.updated_date updated FROM web_organizations o JOIN zip_codes z ON o.zip = z.zip ORDER BY o.name");
foreach ($result as &$r) {
	$r["organization"]	= draw_link("organization_view.php?id=" . $r["id"], format_string($r["organization"], 50));
	$r["location"]		= $r["city"] . ", " . $r["state"];
	$r["updated"]		= "<nobr>" . format_date($r["updated"]) . "</nobr>";
}
echo $t->draw($result);

echo drawBottom();
?>