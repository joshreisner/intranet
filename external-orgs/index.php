<?php
include("../include.php");

drawTop();

//main table
echo drawTableStart();
echo drawHeaderRow("Types List", 2);

$types = db_query("SELECT t.id, t.title, (SELECT COUNT(*) FROM external_orgs_to_types o2t JOIN external_orgs o ON o2t.org_id = o.id WHERE t.id = o2t.type_id AND o.is_active = 1) count FROM external_orgs_types t ORDER BY t.title");
if (db_found($types)) {?>
	<tr>
		<th>Type</th>
		<th class="r">Count</th>
	</tr>
<? while ($t = db_fetch($types)) {?>
	<tr>
		<td><a href="type.php?id=<?=$t["id"]?>"><?=$t["title"]?></a></td>
		<td class="r"><?=$t["count"]?></td>
	</tr>
	<? }
} else {
	echo drawEmptyResult("There are no types added yet.");
}
echo drawTableEnd();

//add new
include("edit/index.php");
drawBottom();?>