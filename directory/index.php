<? include("../include.php");
drawTop();

$t = new table("Organizations");
$t->col("organization");
$t->col("location");
$t->col("updated", "r");
$t->title(drawHeader());

$result = db_table("SELECT 
						o.id,
						o.name organization, 
						o.phone,
						z.city, 
						z.state, 
						o.zip,
						o.lastUpdatedOn updated
					FROM web_organizations o
					JOIN zip_codes z ON o.zip = z.zip
					ORDER BY o.name");
foreach ($result as &$r) {
	$r["organization"] = draw_link("organization_view.php?id=" . $r["id"], format_string($r["organization"], 50));
	$r["location"] = $r["city"] . ", " . $r["state"];
	$r["updated"] = "<nobr>" . format_date($r["updated"]) . "</nobr>";
}
echo $t->draw($result);

echo drawBottom();
?>