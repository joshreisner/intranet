<?php
include("../include.php");

echo drawTop();

//main table
$result = db_table("SELECT t.id, t.title, (SELECT COUNT(*) FROM external_orgs_to_types o2t JOIN external_orgs o ON o2t.org_id = o.id WHERE t.id = o2t.type_id AND o.is_active = 1) count FROM external_orgs_types t ORDER BY t.title");
$t = new table('external_orgs_types', drawHeader());
$t->col('type');
$t->col('count', 'r');
foreach ($result as &$r) $r['type'] = draw_link('type.php?id=' . $r["id"], $r["title"]);
echo $t->draw($result, 'There are no types added yet.');

//add new
include("edit/index.php");
echo drawBottom();
?>